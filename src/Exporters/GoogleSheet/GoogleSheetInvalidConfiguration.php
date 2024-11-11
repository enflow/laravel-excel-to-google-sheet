<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet;

use Exception;

class GoogleSheetInvalidConfiguration extends Exception
{
    public static function credentialsJsonDoesNotExist(string $path): static
    {
        return new static("Could not find a credentials file at `{$path}`.");
    }

    public static function sheetDoesntExist(string $sheet): static
    {
        return new static("Sheet `{$sheet}` does not exist in spreadsheet.");
    }
}
