<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet;

use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\Exceptions\InvalidConfiguration;
use Google\Service\Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ValueRange;

class GoogleSheet
{
    public function __construct(
        protected Sheets $service,
    ) {}

    public function clear(string $spreadsheetId, string $sheetName): void
    {
        $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);

        $sheet = collect($spreadsheet->getSheets())->firstWhere(fn (Sheets\Sheet $sheet) => $sheet->getProperties()->getTitle() === $sheetName);
        throw_unless($sheet, InvalidConfiguration::sheetDoesntExist($sheetName));

        $sheetId = $sheet->getProperties()->getSheetId();

        $this->service->spreadsheets->batchUpdate(
            spreadsheetId: $spreadsheetId,
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

    public function insert(string $spreadsheetId, string $range, array $values): void
    {
        $this->service->spreadsheets_values->append(
            spreadsheetId: $spreadsheetId,
            range: $range,
            postBody: new Google_Service_Sheets_ValueRange([
                'values' => $values,
            ]),
            optParams: [
                'valueInputOption' => 'USER_ENTERED',
            ],
        );
    }
}
