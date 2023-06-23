<?php

namespace Enflow\LaravelExcelToGoogleSheet\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    public static function credentialsJsonDoesNotExist(string $path): static
    {
        return new static("Could not find a credentials file at `{$path}`.");
    }

    public static function exportMustImplementExportableToGoogleSheet(string $class): static
    {
        return new static("Class `{$class}` must implement `Enflow\LaravelExcelToGoogleSheet\ExportableToGoogleSheet`.");
    }

    public static function exportDoesNotExist(string $class)
    {
        return new static("Class `{$class}` does not exist.");
    }
}
