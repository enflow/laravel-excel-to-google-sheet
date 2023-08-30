<?php

return [
    /**
     * The classes that should be available as exports.
     */
    'exports' => [
        // 'teams' => \App\Exports\TeamsExport::class,
    ],

    /*
     * Path to the client secret json file. Take a look at the README of this package
     * to learn how to get this file. You can also pass the credentials as an array
     * instead of a file path.
     */
    'service_account_credentials_json' => storage_path('secrets/google-service-account.json'),

    /**
     * When set, the given memory limit will be used for the duration of the export.
     */
    'memory_limit' => null,
];
