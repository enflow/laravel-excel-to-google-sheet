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
        ],

        'google-bigquery' => [
            /*
             * Path to the client secret json file. Take a look at the README of this package
             * to learn how to get this file. You can also pass the credentials as an array
             * instead of a file path.
             */
            'service_account_credentials_json' => storage_path('secrets/google-service-account.json'),
        ],
    ],

    /**
     * The pushers that are available to push exports to.
     */
    'pushers' => [
        'google-sheet' => [
            'interface' => \Enflow\LaravelExcelExporter\Exporters\GoogleSheet\ExportableToGoogleSheet::class,
            'pusher' => \Enflow\LaravelExcelExporter\Exporters\GoogleSheet\GoogleSheetPusher::class,
        ],

        'google-bigquery' => [
            'interface' => \Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\ExportableToGoogleBigQuery::class,
            'pusher' => \Enflow\LaravelExcelExporter\Exporters\GoogleBigQuery\GoogleBigQueryPusher::class,
        ],
    ],

    /**
     * When set, the given memory limit will be used for the duration of the export.
     */
    'memory_limit' => null,
];
