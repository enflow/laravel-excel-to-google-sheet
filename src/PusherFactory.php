<?php

namespace Enflow\LaravelExcelExporter;

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
