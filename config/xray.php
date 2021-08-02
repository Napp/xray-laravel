<?php

declare(strict_types=1);

return [
    'name' => env('AWS_XRAY_SERVICE_NAME'),

    'enabled' => env('AWS_XRAY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Ignoring any possible error
    |--------------------------------------------------------------------------
    |
    | In production, it will possibly need to ignore error from Xray.
    |
    */
    'ignore_error' => env('AWS_XRAY_IGNORE_ERROR', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored routes
    |--------------------------------------------------------------------------
    |
    | Comma separated value to ignore record Xray.
    | Default will allow all routes to trace.
    |
    */
    'route_filters' => explode(',', env('AWS_XRAY_ROUTE_FILTERS', '')),

    /*
    |--------------------------------------------------------------------------
    | Submission method
    |--------------------------------------------------------------------------
    |
    | This is where you can set the data submission method.
    | If [AWS_XRAY_DAEMON_HOST] is set, it will automatically using [DaemonSegmentSubmitter]
    | Supported classes: "APISegmentSubmitter", "DaemonSegmentSubmitter"
    |
    */
    'submitter' => is_null(env('AWS_XRAY_DAEMON_HOST'))
        ? \Napp\Xray\Submission\APISegmentSubmitter::class
        : \Napp\Xray\Submission\DaemonSegmentSubmitter::class,

    /*
    |--------------------------------------------------------------------------
    | Enable Database Query
    |--------------------------------------------------------------------------
    */
    'db_query' => env('AWS_XRAY_ENABLE_DB_QUERY', true),

    /*
    |--------------------------------------------------------------------------
    | Enable Database Query Bindings
    |--------------------------------------------------------------------------
    */
    'db_bindings' => env('AWS_XRAY_ENABLE_DB_QUERY_BINDINGS', false),

    /*
    |--------------------------------------------------------------------------
    | Erase Database Query Value
    |--------------------------------------------------------------------------
    */
    'db_erase_query' => env('AWS_XRAY_ERASE_DB_QUERY_VALUE', false),

    /*
    |--------------------------------------------------------------------------
    | Trace Queue Jobs
    |--------------------------------------------------------------------------
    */
    'job' => env('AWS_XRAY_ENABLE_JOB', true),

    /*
    |--------------------------------------------------------------------------
    | Trace Views
    |--------------------------------------------------------------------------
    */
    'view' => env('AWS_XRAY_ENABLE_VIEW', true),

    /*
    |--------------------------------------------------------------------------
    | Trace Routes
    |--------------------------------------------------------------------------
    */
    'route' => env('AWS_XRAY_ENABLE_ROUTE', true),

    /*
    |--------------------------------------------------------------------------
    | Trace Routes
    |--------------------------------------------------------------------------
    */
    'framework' => env('AWS_XRAY_ENABLE_FRAMEWORK', true),

    /*
    |--------------------------------------------------------------------------
    | Sampled Rate If Not Receiving Header
    |--------------------------------------------------------------------------
    */
    'sample_rate' => env('AWS_XRAY_SAMPLE_RATE', 100),

    /*
    |--------------------------------------------------------------------------
    | AWS, only needed if "APISegmentSubmitter" submitter is chosen
    |--------------------------------------------------------------------------
    */
    'aws' => [
        'region' => env('AWS_XRAY_REGION') ?? env('AWS_DEFAULT_REGION'),
        'version' => env('AWS_XRAY_VERSION', 'latest'),
        'signature_version' => env('AWS_XRAY_SIGNATURE_VERSION', 'v4'),
        'credentials' => [
            'key' => env('AWS_XRAY_ACCESS_KEY_ID') ?? env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_XRAY_SECRET_ACCESS_KEY') ?? env('AWS_SECRET_ACCESS_KEY'),
            'token' => env('AWS_XRAY_TOKEN'),
            'expires' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Daemon, only needed if "DaemonSegmentSubmitter" submitter is chosen
    |--------------------------------------------------------------------------
    */
    'daemon_host' => env('AWS_XRAY_DAEMON_HOST'),
    'daemon_port' => env('AWS_XRAY_DAEMON_PORT', '2000'),
];
