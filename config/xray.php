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
            'expires' => now()->addDay()->unix(),
        ],
    ],
];
