# Push Laravel Excel exporters

[![Latest Version on Packagist](https://img.shields.io/packagist/v/enflow/laravel-excel-exporter.svg?style=flat-square)](https://packagist.org/packages/enflow/laravel-excel-exporter)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/enflow/laravel-excel-exporter.svg?style=flat-square)](https://packagist.org/packages/enflow/laravel-excel-exporter)

The `enflow/laravel-excel-exporter` package provides an easy way to push Laravel Excel exporters to Google Sheet and Google BigQuery. 
Use-cases include creating a Laravel Export to be exported in your application layer, which also needs to be periodically synced to a remote Google Sheet or Google BigQuery table.

## Installation
You can install the package via composer:

``` bash
composer require enflow/laravel-excel-exporter
```

## Authentication
The package uses the [Google Client PHP library](https://github.com/googleapis/google-api-php-client) in the background. Authenticating with Google requires a Google Cloud Console account. 
Via the Google Cloud Console account, you must have a valid project, where the `Google Spreadsheet API` is enabled. To export the required JSON credentials, you can follow the steps below:

1) Go to the [Google Cloud Console](https://console.cloud.google.com/)
2) Create a new project or select an existing project
3) Go to `APIs & Services` > `Credentials`
4) Click `Create Credentials` > `Service account`
5) Choose `Spreadsheet API` for the scope.
6) Fill in the required fields and click `Create`
7) Select the created service account and click `Add key` > `Create new key`
8) Select `JSON` and click `Create`
9) The JSON file will be downloaded to your computer
10) Copy the contents of the JSON file and place it in a secure place. We recommend `storage/secrets/google-service-account.json`

## Implementation

To start, publish the config file:

```bash
php artisan vendor:publish --provider="Enflow\LaravelExcelExporter\LaravelExcelExporterServiceProvider" --tag="config"
```

After, you can add your existing Laravel Excel export classes to the `exports` array:
```php
'exports' => [
    'teams' => \App\Exports\TeamsExport::class,
],
```

After setting up the exports, we recommend running `php artisan excel-exporter:push` to validate the exports are pushed correctly.

To periodically schedule a push from your Laravel Excel export to the defined exporter, you can schedule the `Enflow\LaravelExcelExporter\Commands\PushAll` command. This will send all defined exports to their defined export destination. For instance:

```php
use Enflow\LaravelExcelExporter\Commands\PushAll;

$schedule->command(PushAll::class)->dailyAt(3)->environments('production');
```

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
