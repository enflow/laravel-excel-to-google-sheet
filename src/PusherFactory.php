<?php

namespace Enflow\LaravelExcelExporter;

use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetPusher;
use Exception;
use Illuminate\Support\Collection;

class PusherFactory
{
    protected static array $exporters = [];

    public static function make(Exportable $export): Collection
    {
        return collect(config('excel-exporter.pushers'))
            ->filter(fn(array $pusher) => $export instanceof $pusher['interface'])
            ->map(fn(array $pusher) => app($pusher['pusher'], ['export' => $export]))
            ->values();
    }

    public static function register(string $exporter, string $pusher): void
    {
        static::$exporters[$exporter] = $pusher;
    }
}
