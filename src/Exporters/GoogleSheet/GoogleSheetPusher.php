<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet;

use Enflow\LaravelExcelExporter\Exportable;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\Exceptions\InvalidConfiguration;
use Enflow\LaravelExcelExporter\Pusher;
use Google\Exception as GoogleException;
use Illuminate\Support\LazyCollection;

class GoogleSheetPusher implements Pusher
{
    public function __construct(
        protected Exportable $export,
    ) {}

    public function clear(): void
    {
        dd('would clear sheet');
        $this->sheet()->clear($this->export->googleSpreadsheetId(), $this->export->title());
    }

    public function insert(LazyCollection $collection): void
    {
        dd('would insert data');
        try {
            $collection->each(function (LazyCollection $chunk) {
                // Send the data to the Google Sheet.
                $this->sheet()->insert(
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

    protected function sheet(): GoogleSheet
    {
        $config = config('excel-exporter.exporters.google-sheet');

        if (! is_array($config['service_account_credentials_json']) && ! file_exists($config['service_account_credentials_json'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($config['service_account_credentials_json']);
        }

        return app(GoogleSheet::class, [
            'service' => GoogleSheetServiceFactory::createForConfig($config),
        ]);
    }
}
