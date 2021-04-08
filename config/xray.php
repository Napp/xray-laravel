<?php

declare(strict_types=1);

return [
    'enabled' => env('XRAY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Submission method
    |--------------------------------------------------------------------------
    |
    | This is where you can set the data submission method.
    | Supported classes: "APISegmentSubmitter", "DaemonSegmentSubmitter"
    |
    */
    'submitter' => \Napp\Xray\Submission\APISegmentSubmitter::class,

    /*
    |--------------------------------------------------------------------------
    | Enable Database Query
    |--------------------------------------------------------------------------
    */
    'db_query' => env('XRAY_DB_QUERY', true),

    /*
    |--------------------------------------------------------------------------
    | Enable Database Query Bindings
    |--------------------------------------------------------------------------
    */
    'db_bindings' => env('XRAY_DB_QUERY_BINDINGS', false),

    /*
    |--------------------------------------------------------------------------
    | Trace Queue Jobs
    |--------------------------------------------------------------------------
    */
    'job' => env('XRAY_JOB', true),

    /*
    |--------------------------------------------------------------------------
    | Trace Views
    |--------------------------------------------------------------------------
    */
    'view' => env('XRAY_VIEW', true),

    /*
    |--------------------------------------------------------------------------
    | Trace Routes
    |--------------------------------------------------------------------------
    */
    'route' => env('XRAY_ROUTE', true),

    /*
    |--------------------------------------------------------------------------
    | Trace Routes
    |--------------------------------------------------------------------------
    */
    'framework' => env('XRAY_FRAMEWORK', true),

    /*
    |--------------------------------------------------------------------------
    | AWS, only needed if "APISegmentSubmitter" submitter is chosen
    |--------------------------------------------------------------------------
    */
    'aws' => [
        'region' => env('XRAY_AWS_REGION') ?? env('AWS_DEFAULT_REGION'),
        'version' => env('XRAY_AWS_VERSION', 'latest'),
        'signature_version' => env('XRAY_AWS_SIGNATURE_VERSION', 'v4'),
        'credentials' => [
            'key' => env('XRAY_AWS_ACCESS_KEY_ID') ?? env('AWS_ACCESS_KEY_ID'),
            'secret' => env('XRAY_AWS_SECRET_ACCESS_KEY') ?? env('AWS_SECRET_ACCESS_KEY'),
            'token' => env('XRAY_AWS_TOKEN'),
            'expires' => '',
        ],
    ],
];
