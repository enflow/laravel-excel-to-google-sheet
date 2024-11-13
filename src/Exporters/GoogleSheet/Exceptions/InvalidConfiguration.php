<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
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
