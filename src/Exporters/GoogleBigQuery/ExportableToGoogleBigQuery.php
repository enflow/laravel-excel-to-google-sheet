<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery;

use Enflow\LaravelExcelExporter\Exportable;

interface ExportableToGoogleBigQuery extends Exportable
{
    public function googleBigQueryProjectId(): string;

    public function googleBigQueryDatasetId(): string;

    public function googleBigQueryTableId(): string;
}
