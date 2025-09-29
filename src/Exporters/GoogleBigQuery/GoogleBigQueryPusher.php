<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery;

use Enflow\LaravelExcelExporter\Pusher;
use Google\Service\Bigquery;
use Google\Service\Bigquery\TableDataInsertAllRequestRows;
use Google\Service\Exception as GoogleServiceException;
use Google_Service_Bigquery_Table;
use Google_Service_Bigquery_TableDataInsertAllRequest;
use Google_Service_Bigquery_TableReference;
use Illuminate\Support\LazyCollection;

class GoogleBigQueryPusher implements Pusher
{
    public function __construct(
        protected Bigquery $service,
        protected string $projectId,
        protected string $datasetId,
        protected ExportableToGoogleBigQuery $export,
    ) {}

    public function clear(): void
    {
        // Delete the existing table. Handle non-existence gracefully.
        try {
            $this->service->tables->delete(
                projectId: $this->projectId,
                datasetId: $this->datasetId,
                tableId: $this->export->googleBigQueryTableId(),
            );
        } catch (GoogleServiceException $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }

        // Create a new empty table
        $table = new Google_Service_Bigquery_Table();
        $tableReference = new Google_Service_Bigquery_TableReference();
        $tableReference->setProjectId($this->projectId);
        $tableReference->setDatasetId($this->datasetId);
        $tableReference->setTableId($this->export->googleBigQueryTableId());
        $table->setTableReference($tableReference);

        $this->service->tables->insert(
            projectId: $this->projectId,
            datasetId: $this->datasetId,
            postBody: $table,
        );
    }

    public function insert(LazyCollection $chunk): void
    {
        $insertRows = $chunk->take(2)->map(function (array $row) {
            // row contains:
//            array:2 [
//                0 => "Email"
//  1 => "DEBUG"
//]

            $insertRow = new TableDataInsertAllRequestRows();
            $insertRow->setJson($row);

            return $insertRow;
        })->all();

        $request = new Google_Service_Bigquery_TableDataInsertAllRequest();
        $request->setRows($insertRows);

        $this->service->tabledata->insertAll(
            projectId: $this->projectId,
            datasetId: $this->datasetId,
            tableId: $this->export->googleBigQueryTableId(),
            postBody: $request,
        );
    }
}
