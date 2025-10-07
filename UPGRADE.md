# Upgrade Guide

This guide will help you migrate from `enflow/laravel-excel-to-google-sheet` to `enflow/laravel-excel-exporter`.

## Overview

The new `laravel-excel-exporter` package is a complete rewrite that supports both Google Sheets and Google BigQuery exports. It provides a more flexible architecture and better error handling.

## Breaking Changes

### Package Name

**Before:**
```bash
composer require enflow/laravel-excel-to-google-sheet
```

**After:**
```bash
composer require enflow/laravel-excel-exporter
```

### Configuration File

**Before:** `config/laravel-excel-to-google-sheet.php`

**After:** `config/excel-exporter.php`

The configuration structure has changed significantly:

**Before:**
```php
// config/laravel-excel-to-google-sheet.php
return [
    'exports' => [
        'teams' => \App\Exports\TeamsExport::class,
    ],
    'google_sheets' => [
        'service_account_credentials_json' => storage_path('secrets/google-service-account.json'),
    ],
];
```

**After:**
```php
// config/excel-exporter.php
return [
    'exports' => [
        'teams' => \App\Exports\TeamsExport::class,
    ],
    'exporters' => [
        'google-sheet' => [
            'service_account_credentials_json' => storage_path('secrets/google-service-account.json'),
            'interface' => \Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet::class,
            'factory' => \Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetServiceFactory::class,
        ],
        'google-bigquery' => [
            'service_account_credentials_json' => storage_path('secrets/google-service-account.json'),
            'project_id' => 'your-project-id',
            'dataset_id' => 'your-dataset-id',
            'interface' => \Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\ExportableToGoogleBigQuery::class,
            'factory' => \Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\GoogleBigQueryServiceFactory::class,
        ],
    ],
    'memory_limit' => null,
];
```

### Export Class Interface

**Before:**
```php
use Enflow\LaravelExcelToGoogleSheet\ExportableToGoogleSheet;

class TeamsExport implements ExportableToGoogleSheet
{
    public function googleSpreadsheetId(): string
    {
        return 'your-spreadsheet-id';
    }
    
    // ... other export methods
}
```

**After:**
```php
use Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet;

class TeamsExport implements ExportableToGoogleSheet
{
    public function googleSpreadsheetId(): string
    {
        return 'your-spreadsheet-id';
    }
    
    // ... other export methods
}
```

### Console Commands

**Before:**
```bash
php artisan push-export-to-google-sheets
```

**After:**
```bash
php artisan excel-exporter:push
```

**Before:**
```bash
php artisan push-all-exports-to-google-sheets
```

**After:**
```bash
php artisan excel-exporter:push-all
```

### Scheduling

**Before:**
```php
use Enflow\LaravelExcelToGoogleSheet\PushExportsToGoogleSheets;

$schedule->command(PushExportsToGoogleSheets::class)->dailyAt(3)->environments('production');
```

**After:**
```php
use Enflow\LaravelExcelExporter\Commands\PushAll;

$schedule->command(PushAll::class)->dailyAt('03:00')->environments('production');
```

## Migration Steps

1. **Remove the old package:**
   ```bash
   composer remove enflow/laravel-excel-to-google-sheet
   ```

2. **Install the new package:**
   ```bash
   composer require enflow/laravel-excel-exporter
   ```

3. **Publish the new configuration:**
   ```bash
   php artisan vendor:publish --tag="laravel-excel-exporter-config" --force
   ```

4. **Update your configuration:**
   - Copy your exports from the old config to the new `exports` array
   - Move your Google Sheets credentials to the `google-sheet` exporter configuration
   - If you want to use BigQuery, configure the `google-bigquery` exporter section

5. **Update your export classes:**
   - Change the namespace from `Enflow\LaravelExcelToGoogleSheet\ExportableToGoogleSheet` to `Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet`
   - The interface methods remain the same

6. **Update your scheduled commands:**
   - Replace the old command class with `Enflow\LaravelExcelExporter\Commands\PushAll`
   - Update the command name in your schedule

7. **Test your exports:**
   ```bash
   php artisan excel-exporter:push
   ```

## New Features

The new package includes several improvements:

- **BigQuery Support**: Export directly to Google BigQuery tables
- **Better Error Handling**: Improved retry logic and error reporting
- **Memory Management**: Configurable memory limits for large exports
- **Modern Architecture**: Built with Spatie's Laravel Package Tools
- **Better Testing**: More comprehensive test coverage

## Support

If you encounter any issues during the migration, please:

1. Check the [README](README.md) for the latest documentation
2. Review the [configuration file](config/excel-exporter.php) for all available options
3. Open an issue on the [GitHub repository](https://github.com/enflow/laravel-excel-exporter)

## Deprecation Notice

The `enflow/laravel-excel-to-google-sheet` package will be archived and no longer maintained. Please migrate to `enflow/laravel-excel-exporter` as soon as possible.
