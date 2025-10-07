<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery;

use Enflow\LaravelExcelExporter\Exportable;

interface ExportableToGoogleBigQuery extends Exportable
{
    public function googleBigQueryTableId(): string;

    public function googleBigQuerySchema(): array;
}
