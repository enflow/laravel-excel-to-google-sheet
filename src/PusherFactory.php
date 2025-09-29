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
        return collect(static::$exporters)
            ->filter(fn(string $pusher, string $exporter) => $export instanceof $exporter)
            ->map(fn($pusher) => app($pusher, ['export' => $export]))
            ->values();
    }

    public static function register(string $exporter, string $pusher): void
    {
        static::$exporters[$exporter] = $pusher;
    }
}
