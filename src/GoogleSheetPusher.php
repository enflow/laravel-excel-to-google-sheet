<?php

namespace Enflow\LaravelExcelToGoogleSheet;

use Google\Exception as GoogleException;
use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Writer;

class GoogleSheetPusher
{
    public function __construct(
        protected GoogleSheetService $googleSheetService,
    )
    {

    }

    public function __invoke(ExportableToGoogleSheet $export): void
    {
        if (method_exists($export, 'prepare')) {
            $export->prepare();
        }

        $writer = app(Writer::class)->export($export, Excel::CSV);
        $handle = fopen($writer->getLocalPath(), 'r');

        $sheetName = $export->title();

        // First, ensure the sheet is empty.
        $this->googleSheetService->clearSheet($export->googleSpreadsheetId(), $sheetName);

        try {
            LazyCollection::make(function () use ($handle) {
                while ($line = fgetcsv($handle)) {
                    yield $line;
                }
            })->chunk(5000)->each(function (LazyCollection $chunk) use ($export, $sheetName) {
                // Send the data to the Google Sheet.
                $this->googleSheetService->insert(
                    spreadsheetId: $export->googleSpreadsheetId(),
                    range: $sheetName,
                    values: $chunk->values()->all(),
                );
            });

            if (is_resource($handle)) {
                fclose($handle);
            }
        } catch (GoogleException $e) {
            // Clear the complete sheet if (a chunk) fails.
            // We don't want to end up with a half-filled sheet.
            $this->googleSheetService->clearSheet($export->googleSpreadsheetId(), $sheetName);

            throw $e;
        }
    }
}
