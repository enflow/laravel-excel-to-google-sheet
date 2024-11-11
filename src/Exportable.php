<?php

namespace Enflow\LaravelExcelExporter;

use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetPusher;
use Maatwebsite\Excel\Concerns\WithTitle;

interface Exportable extends WithTitle
{

}