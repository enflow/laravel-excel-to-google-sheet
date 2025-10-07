<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery;

use Enflow\LaravelExcelExporter\Pusher;
use Google\Service\Bigquery;
use Google\Service\Bigquery\Job;
use Google\Service\Bigquery\JobConfiguration;
use Google\Service\Bigquery\JobConfigurationQuery;
use Google\Service\Bigquery\JobReference;
use Google\Service\Bigquery\TableDataInsertAllRequest;
use Google\Service\Bigquery\TableDataInsertAllRequestRows;
use Google\Service\Exception as GoogleServiceException;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GoogleBigQueryPusher implements Pusher
{
    public function __construct(
        protected Bigquery                   $service,
        protected string                     $projectId,
        protected string                     $datasetId,
        protected ExportableToGoogleBigQuery $export,
    )
    {
    }

    public function clear(): void
    {
        $columns = collect($this->export->googleBigQuerySchema())
            ->map(fn($type, $name) => sprintf('`%s` %s', $name, strtoupper($type)))
            ->implode(', ');

        $this->awaitJobDone(
            jobId: $this->runQueryJob(sprintf('CREATE OR REPLACE TABLE %s (%s)', $this->tableRef(), $columns)),
        );
    }

    public function insert(LazyCollection $chunk, int $index): void
    {
        $schema = array_keys($this->export->googleBigQuerySchema());

        $rows = $chunk
            ->when($index === 0, fn($c) => $c->skip(1)) // drop if no header
            ->map(function (array $row, int $i) use ($index, $schema) {
                $insert = new TableDataInsertAllRequestRows;
                $insert->setJson(array_combine($schema, $row));
                $insert->setInsertId($index . ':' . $i);

                return $insert;
            })
            ->values()
            ->all();

        if (empty($rows)) {
            return;
        }

        $request = new TableDataInsertAllRequest;
        $request->setRows($rows);

        retry(
            times: 50,
            callback: function () use ($request) {
                try {
                    $response = $this->service->tabledata->insertAll(
                        projectId: $this->projectId,
                        datasetId: $this->datasetId,
                        tableId: $this->export->googleBigQueryTableId(),
                        postBody: $request,
                    );
                } catch (GoogleServiceException $e) {
                    if ($this->isRetryableThrowable($e)) {
                        throw new RetryableBigQueryException($e->getMessage(), $e->getCode(), $e);
                    }

                    throw $e;
                }

                $errors = $response->getInsertErrors();
                if (! empty($errors)) {
                    if ($this->onlyRetryableInsertErrors($errors)) {
                        throw new RetryableBigQueryException(json_encode($errors, JSON_UNESCAPED_SLASHES));
                    }

                    throw new RuntimeException('BigQuery insertAll failed: ' . json_encode($errors, JSON_UNESCAPED_SLASHES));
                }
            },
            sleepMilliseconds: 750,
            when: fn($e) => $e instanceof RetryableBigQueryException
        );
    }

    protected function tableRef(): string
    {
        return sprintf('`%s.%s.%s`', $this->projectId, $this->datasetId, $this->export->googleBigQueryTableId());
    }

    protected function runQueryJob(string $sql): string
    {
        $job = new Job([
            'jobReference' => new JobReference([
                'projectId' => $this->projectId,
            ]),
            'configuration' => new JobConfiguration([
                'query' => new JobConfigurationQuery([
                    'query' => $sql,
                    'useLegacySql' => false,
                ]),
            ]),
        ]);

        $inserted = $this->service->jobs->insert($this->projectId, $job);

        return $inserted->getJobReference()->getJobId();
    }

    protected function awaitJobDone(string $jobId): void
    {
        retry(
            times: 24,
            callback: function () use ($jobId) {
                $status = $this->service->jobs->get($this->projectId, $jobId);

                if (($status->getStatus()->getState() ?? null) !== 'DONE') {
                    throw new RetryableBigQueryException('DDL not done yet');
                }

                if ($err = $status->getStatus()->getErrorResult()) {
                    $msg = $err['message'] ?? 'DDL failed';
                    $reason = $err['reason'] ?? '';
                    throw new RuntimeException("$msg ($reason)");
                }
            },
            sleepMilliseconds: 500,
            when: fn($e) => $e instanceof RetryableBigQueryException
        );
    }

    protected function onlyRetryableInsertErrors(array $errors): bool
    {
        foreach ($errors as $rowErrors) {
            foreach ($rowErrors as $err) {
                if (! in_array($err['reason'] ?? '', [
                    'backendError',
                    'internalError',
                    'rateLimitExceeded',
                    'quotaExceeded',
                    'resourceUnavailable',
                    'notFound', // includes "Table is truncated."
                ])) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function isRetryableThrowable(Throwable $e): bool
    {
        if (! $e instanceof GoogleServiceException) {
            return false;
        }

        $code = $e->getCode();
        $body = (string)$e->getMessage();

        // Common transient cases after DDL or under load
        if (in_array($code, [429, 500, 502, 503, 504])) {
            return true;
        }

        return $code === 404 && Str::contains($body, [
                '"reason":"notFound"',
                'Table is truncated',
                'Not found: Table',
            ]);
    }
}
