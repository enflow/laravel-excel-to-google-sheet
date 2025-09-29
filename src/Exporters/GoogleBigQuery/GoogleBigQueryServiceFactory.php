<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery;

use Google\Client;
use Google\Service\BigQuery as GoogleBigQuery;

class GoogleBigQueryServiceFactory
{
    public function createForConfig(array $config): GoogleBigQuery
    {
        $googleClient = new Client;
        $googleClient->setApplicationName(config('app.name'));
        $googleClient->setScopes([GoogleBigQuery::BIGQUERY]);
        $googleClient->setAccessType('offline');
        $googleClient->setAuthConfig($config['service_account_credentials_json']);

        return new GoogleBigQuery($googleClient);
    }
}
