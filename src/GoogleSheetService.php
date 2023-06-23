<?php

namespace Enflow\LaravelExcelToGoogleSheet;

use Google\Service\Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ValueRange;

class GoogleSheetService
{
    public function __construct(
        protected Sheets $service,
    ) {

    }

    public function clearSheets(string $spreadsheetId): void
    {
        $this->service->spreadsheets->batchUpdate(
            spreadsheetId: $spreadsheetId,
            postBody: new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
                'requests' => [
                    'updateCells' => [
                        'range' => [
                            'sheetId' => 0,
                        ],
                        'fields' => '*',
                    ],
                ],
            ]),
        );
    }

    public function insert(string $spreadsheetId, string $range, array $values): void
    {
        $this->service->spreadsheets_values->append(
            spreadsheetId: $spreadsheetId,
            range: $range,
            postBody: new Google_Service_Sheets_ValueRange([
                'values' => $values,
            ]),
            optParams: [
                'valueInputOption' => 'RAW',
            ],
        );
    }
}
