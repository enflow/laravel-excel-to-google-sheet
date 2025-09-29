<?php

namespace Enflow\LaravelExcelExporter;

use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetPusher;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetServiceFactory;
use Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\GoogleBigQuery;
use Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\GoogleBigQueryServiceFactory;
use Illuminate\Support\Collection;

class PusherFactory
{
    public static function make(Exportable $export): Collection
    {
        return collect(config('excel-exporter.exporters'))
            ->filter(fn(array $config) => $export instanceof $config['interface'])
            ->map(fn(array $config) => (new $config['factory'])->make(
                config: $config,
                export: $export,
            ))
            ->values();
    }
}
