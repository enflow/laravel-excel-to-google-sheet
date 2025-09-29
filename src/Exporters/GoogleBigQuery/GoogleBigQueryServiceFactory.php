<?php

namespace Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery;

use Google\Client;
use Google\Service\Bigquery as GoogleBigquery;

class GoogleBigQueryServiceFactory
{
    public function make(array $config, ExportableToGoogleBigQuery $export): GoogleBigQueryPusher
    {
        $googleClient = new Client;
        $googleClient->setApplicationName(config('app.name'));
        $googleClient->setScopes([GoogleBigquery::BIGQUERY]);
        $googleClient->setAccessType('offline');
        $googleClient->setAuthConfig($config['service_account_credentials_json']);

        return new GoogleBigQueryPusher(
            service: new GoogleBigquery($googleClient),
            projectId: $config['project_id'],
            datasetId: $config['dataset_id'],
            export: $export,
        );
    }
}
