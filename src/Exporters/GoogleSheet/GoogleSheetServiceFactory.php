<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleSheet;

use Google\Client;
use Google\Service\Sheets as GoogleSheets;

class GoogleSheetServiceFactory
{
    public function make(array $config, ExportableToGoogleSheet $export): GoogleSheetPusher
    {
        $googleClient = new Client;
        $googleClient->setApplicationName(config('app.name'));
        $googleClient->setScopes([GoogleSheets::SPREADSHEETS]);
        $googleClient->setAccessType('offline');
        $googleClient->setAuthConfig($config['service_account_credentials_json']);

        return new GoogleSheetPusher(new GoogleSheets($googleClient), $export);
    }
}
