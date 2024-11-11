<?php

namespace Enflow\LaravelExcelExporter;

use Enflow\LaravelExcelExporter\Commands\Push;
use Enflow\LaravelExcelExporter\Commands\PushAll;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelExcelExporterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-excel-exporter')
            ->hasConfigFile()
            ->hasCommand(Push::class)
            ->hasCommand(PushAll::class);
    }
}
