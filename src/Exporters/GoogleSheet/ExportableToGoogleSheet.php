<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet;

use Enflow\LaravelExcelExporter\Exportable;

interface ExportableToGoogleSheet extends Exportable
{
    public function googleSpreadsheetId(): string;
}
