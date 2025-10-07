# Push Laravel Excel exports

[![Latest Version on Packagist](https://img.shields.io/packagist/v/enflow/laravel-excel-exporter.svg?style=flat-square)](https://packagist.org/packages/enflow/laravel-excel-exporter)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/enflow/laravel-excel-exporter.svg?style=flat-square)](https://packagist.org/packages/enflow/laravel-excel-exporter)

The `enflow/laravel-excel-exporter` package provides an easy way to push Laravel Excel exports to Google Sheets and Google BigQuery.

Common use-cases include exporting data with Laravel Excel in your application while periodically syncing that same export to a Google Sheet or a Google BigQuery table.

## Installation
You can install the package via Composer:

``` bash
composer require enflow/laravel-excel-exporter
```

## Authentication
This package uses the [Google API PHP Client](https://github.com/googleapis/google-api-php-client) under the hood. You will need a Google Cloud project with the correct APIs enabled and a Service Account JSON key.

Create a Service Account JSON key:

1) Go to the [Google Cloud Console](https://console.cloud.google.com/)
2) Create a new project or select an existing project
3) Go to `APIs & Services` > `Credentials`
4) Click `Create Credentials` > `Service account`
5) Enable the APIs you need in the project:
   - For Google Sheets: `Google Sheets API`
   - For Google BigQuery: `BigQuery API`
6) Fill in the required fields and click `Create`
7) Select the created service account and click `Add key` > `Create new key`
8) Select `JSON` and click `Create`
9) The JSON file will be downloaded to your computer
10) Store the downloaded JSON file securely. We recommend `storage/secrets/google-service-account.json`.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="laravel-excel-exporter-config"
```

Register your exports in the `exports` array:
```php
'exports' => [
    // key => Export class (must be resolvable from the container)
    'teams' => \App\Exports\TeamsExport::class,
],
```

You can validate an export by running the push command interactively:

```bash
php artisan excel-exporter:push
```

### Scheduling

To periodically push all configured exports to their destinations, schedule the `PushAll` command. This will send all defined exports to their configured exporter:

```php
use Enflow\LaravelExcelExporter\Commands\PushAll;

$schedule->command(PushAll::class)->dailyAt('03:00')->environments('production');
```

## Exporters

This package supports two exporters:

### Google Sheets

Implement the `ExportableToGoogleSheet` interface on your export class and return the target Spreadsheet ID:

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

### Google BigQuery

Set the project and dataset in the package config. Then implement `ExportableToGoogleBigQuery` on your export class and specify the table and schema:

```php
use Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\ExportableToGoogleBigQuery;

class TeamsExport implements ExportableToGoogleBigQuery
{
    public function googleBigQueryTableId(): string
    {
        return 'teams';
    }

    public function googleBigQuerySchema(): array
    {
        // column => BigQuery type (e.g. STRING, INT64, FLOAT64, BOOL, TIMESTAMP, DATE)
        return [
            'id' => 'INT64',
            'name' => 'STRING',
            'created_at' => 'TIMESTAMP',
        ];
    }
}
```

Notes:
- Ensure the dataset exists in your BigQuery project. The table will be created or replaced automatically based on the schema you provide.
- Configure `project_id`, `dataset_id`, and service account credentials in `config/excel-exporter.php`.

### BigQuery layout guidance

Prefer a single dataset with multiple tables per project. Each export class can target a different table via `googleBigQueryTableId()` while sharing the configured dataset.

## Console commands

- `excel-exporter:push` — interactively push one configured export (or via `--export=key`).
- `excel-exporter:push-all` — push all configured exports.

## Memory usage

Excel exports can be memory intensive. You may set `memory_limit` in `config/excel-exporter.php` for the duration of the push.

## Testing
``` bash
$ composer test
```

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email michel@enflow.nl instead of using the issue tracker.

## Credits
- [Michel Bardelmeijer](https://github.com/mbardelmeijer)
- [All Contributors](../../contributors)

## About Enflow
Enflow is a digital creative agency based in Alphen aan den Rijn, Netherlands. We specialize in developing web applications, mobile applications and websites. You can find more info [on our website](https://enflow.nl/en).

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
