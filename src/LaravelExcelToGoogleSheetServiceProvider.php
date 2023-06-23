<?php

namespace Enflow\LaravelExcelToGoogleSheet;

use Enflow\LaravelExcelToGoogleSheet\Commands\PushExportsToGoogleSheets;
use Enflow\LaravelExcelToGoogleSheet\Exceptions\InvalidConfiguration;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelExcelToGoogleSheetServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-excel-to-google-sheet')
            ->hasConfigFile()
            ->hasCommand(PushExportsToGoogleSheets::class);
    }

    public function bootingPackage(): void
    {
        $this->app->bind(GoogleSheetService::class, function () {
            $config = config('excel-to-google-sheet');
            $this->guardAgainstInvalidConfiguration($config);

            return GoogleSheetServiceFactory::createForConfig($config);
        });

        $this->app->bind(GoogleSheetPusher::class, fn () => new GoogleSheetPusher($this->app->make(GoogleSheetService::class)));
    }

    protected function guardAgainstInvalidConfiguration(array $config = null): void
    {
        if (is_array($config['service_account_credentials_json'])) {
            return;
        }

        if (! file_exists($config['service_account_credentials_json'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($config['service_account_credentials_json']);
        }
    }
}
