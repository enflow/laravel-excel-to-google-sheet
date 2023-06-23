<?php

namespace Enflow\LaravelExcelToGoogleSheet;

use Google\Exception as GoogleException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

class GoogleSheetPusher
{
    public function __construct(
        protected GoogleSheetService $googleSheetService,
    ) {

    }

    public function __invoke(ExportableToGoogleSheet $export): void
    {
        if (method_exists($export, 'prepare')) {
            $export->prepare();
        }

        $rawExport = ExcelFacade::raw($export, Excel::CSV);

        $sheetName = $export->title();

        // First, ensure the sheet is empty.
        $this->googleSheetService->clearSheet($export->googleSpreadsheetId(), $sheetName);

        try {
            collect(explode("\n", $rawExport))
                ->map(fn (string $row) => str_getcsv($row))
                ->chunk(5000)
                ->each(function (Collection $chunk) use ($export, $sheetName) {
                    // Send the data to the Google Sheet.
                    $this->googleSheetService->insert(
                        spreadsheetId: $export->googleSpreadsheetId(),
                        range: $sheetName,
                        values: $chunk->values()->all(),
                    );
                });
        } catch (GoogleException $e) {
            // Clear the complete sheet if (a chunk) fails.
            // We don't want to end up with a half-filled sheet.
            $this->googleSheetService->clearSheet($export->googleSpreadsheetId(), $sheetName);

            throw $e;
        }
    }
}
