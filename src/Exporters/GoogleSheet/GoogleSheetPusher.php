<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet;

use Enflow\LaravelExcelExporter\Exportable;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetService;
use Enflow\LaravelExcelExporter\Pusher;
use Google\Exception as GoogleException;
use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Writer;

class GoogleSheetPusher implements Pusher
{
    public function __construct(
        protected Exportable         $export,
    )
    {
        $config = config('excel-to-google-sheet');
        $this->guardAgainstInvalidConfiguration($config);

        $this->service = app(GoogleSheetService::class, [

        ]);
    }

    public function clear(): void
    {
        $this->service->clearSheet($this->export->googleSpreadsheetId(), $this->export->title());
    }

    public function insert(LazyCollection $collection): void
    {
        try {
            $collection->each(function (LazyCollection $chunk) {
                // Send the data to the Google Sheet.
                $this->service->insert(
                    spreadsheetId: $this->export->googleSpreadsheetId(),
                    range: $this->export->title(),
                    values: $chunk->values()->all(),
                );
            });
        } catch (GoogleException $e) {
            // Clear the complete sheet if (a chunk) fails.
            // We don't want to end up with a half-filled sheet.
            $this->clear();

            throw $e;
        }
    }
}
