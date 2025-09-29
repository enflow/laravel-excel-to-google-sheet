<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    public static function sheetDoesntExist(string $sheet): static
    {
        return new static("Sheet `{$sheet}` does not exist in spreadsheet.");
    }
}
