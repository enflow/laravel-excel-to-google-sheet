<?php

namespace Enflow\LaravelExcelExporter;

use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetPusher;
use Exception;

class PusherFactory
{
    public static function make(Exportable $export): Pusher
    {
        $class = match (true) {
            $export instanceof ExportableToGoogleSheet => GoogleSheetPusher::class,
            default => throw new Exception('Must implement specific pusher for exportable'),
        };

        return app($class, ['export' => $export]);
    }
}