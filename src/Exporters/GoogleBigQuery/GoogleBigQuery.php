<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery;

use Enflow\LaravelExcelExporter\Exportable;
use Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\Exceptions\InvalidConfiguration;
use Enflow\LaravelExcelExporter\Pusher;
use Exception;
use Google\Exception as GoogleException;
use Google\Service\Bigquery;
use Google\Service\Bigquery\TableDataInsertAllRequestRows;
use Google\Service\Exception as GoogleServiceException;
use Google_Service_Bigquery_Dataset;
use Google_Service_Bigquery_DatasetReference;
use Google_Service_Bigquery_Table;
use Google_Service_Bigquery_TableDataInsertAllRequest;
use Google_Service_Bigquery_TableDataInsertAllRequest_Rows;
use Google_Service_Bigquery_TableReference;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;

class GoogleBigQuery implements Pusher
{
    public function __construct(
        protected Bigquery $service,
        protected ExportableToGoogleBigQuery $export,
    ) {}

    public function clear(): void
    {
        $projectId = $this->export->googleBigQueryProjectId();
        $datasetId = $this->export->googleBigQueryDatasetId();
        $tableName = $this->export->googleBigQueryTableName();

        // Delete the existing table. Handle non-existence gracefully.
        try {
            $this->service->tables->delete($projectId, $datasetId, $tableName);
        } catch (GoogleServiceException $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }

        // Create a new empty table
        $table = new Google_Service_Bigquery_Table();
        $tableReference = new Google_Service_Bigquery_TableReference();
        $tableReference->setProjectId($projectId);
        $tableReference->setDatasetId($datasetId);
        $tableReference->setTableId($tableName);
        $table->setTableReference($tableReference);

        $this->service->tables->insert(
            projectId: $projectId,
            datasetId: $datasetId,
            postBody: $table,
        );
    }

    public function insert(LazyCollection $chunk): void
    {
        $insertRows = $chunk->map(function (array $row) {
            $insertRow = new TableDataInsertAllRequestRows();
            $insertRow->setJson($row);

            return $insertRow;
        })->all();

        $request = new Google_Service_Bigquery_TableDataInsertAllRequest();
        $request->setRows($insertRows);

        $this->service->tabledata->insertAll(
            projectId: $this->export->googleBigQueryProjectId(),
            datasetId: $this->export->googleBigQueryDatasetId(),
            tableId: $this->export->googleBigQueryTableName(),
            postBody: $request,
        );
    }
}
