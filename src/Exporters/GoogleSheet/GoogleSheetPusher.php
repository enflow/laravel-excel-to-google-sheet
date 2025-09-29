<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet;

use Enflow\LaravelExcelExporter\Exportable;
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\Exceptions\InvalidConfiguration;
use Enflow\LaravelExcelExporter\Pusher;
use Google\Exception as GoogleException;
use Google\Service\Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ValueRange;
use Illuminate\Support\LazyCollection;

class GoogleSheetPusher implements Pusher
{
    public function __construct(
        protected Sheets $service,
        protected ExportableToGoogleSheet $export,
    ) {}

    public function clear(): void
    {
        $spreadsheet = $this->service->spreadsheets->get($this->export->googleSpreadsheetId());

        $sheet = collect($spreadsheet->getSheets())->firstWhere(fn (Sheets\Sheet $sheet) => $sheet->getProperties()->getTitle() === $this->export->title());
        throw_unless($sheet, InvalidConfiguration::sheetDoesntExist($this->export->title()));

        $sheetId = $sheet->getProperties()->getSheetId();

        $this->service->spreadsheets->batchUpdate(
            spreadsheetId: $this->export->googleSpreadsheetId(),
            postBody: new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
                'requests' => [
                    'updateCells' => [
                        'range' => [
                            'sheetId' => $sheetId,
                        ],
                        'fields' => '*',
                    ],
                ],
            ]),
        );
    }

    public function insert(LazyCollection $chunk): void
    {
        // Send the data to the Google Sheet.
        $this->service->spreadsheets_values->append(
            spreadsheetId: $this->export->googleSpreadsheetId(),
            range: $this->export->title(),
            postBody: new Google_Service_Sheets_ValueRange([
                'values' => $chunk->values()->all(),
            ]),
            optParams: [
                'valueInputOption' => 'USER_ENTERED',
            ],
        );
    }
}
