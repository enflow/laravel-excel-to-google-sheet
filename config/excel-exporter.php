<?php

return [
    /**
     * The classes that should be available as exports.
     */
    'exports' => [
        // 'teams' => \App\Exports\TeamsExport::class,
    ],

    /**
     * The configuration for the exporters.
     */
    'exporters' => [
        'google-sheet' => [
            /*
             * Path to the client secret json file. Take a look at the README of this package
             * to learn how to get this file. You can also pass the credentials as an array
             * instead of a file path.
             */
            'service_account_credentials_json' => storage_path('secrets/google-service-account.json'),

            'interface' => \Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet::class,
            'factory' => \Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetServiceFactory::class,
        ],

        'google-bigquery' => [
            /*
             * Path to the client secret json file. Take a look at the README of this package
             * to learn how to get this file. You can also pass the credentials as an array
             * instead of a file path.
             */
            'service_account_credentials_json' => storage_path('secrets/google-service-account.json'),

            'project_id' => 'your-project-id',
            'dataset_id' => 'your-dataset-id',

            'interface' => \Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\ExportableToGoogleBigQuery::class,
            'factory' => \Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\GoogleBigQueryServiceFactory::class,
        ],
    ],

    /**
     * When set, the given memory limit will be used for the duration of the export.
     */
    'memory_limit' => null,
];
