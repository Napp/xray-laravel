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
    | User resolver method
    |--------------------------------------------------------------------------
    |
    | Here you can set a class to find the logged-in user identifier.
    | Supported classes: "AuthIdentifier"
    |
    */
    'user-resolver' => \Napp\Xray\Resolvers\AuthIdentifier::class,

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
    | Trace Commands
    |--------------------------------------------------------------------------
    */
    'command' => env('XRAY_COMMAND', true),

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
    | Trace Cache
    |--------------------------------------------------------------------------
    */
    'cache' => env('XRAY_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Use LAMBDA_INVOCATION_CONTEXT
    |--------------------------------------------------------------------------
    */
    'use_lambda_invocation_context' => env('XRAY_USE_LAMBDA_INVOCATION_CONTEXT', false),

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
            'token' => env('XRAY_AWS_TOKEN') ?? env('AWS_SESSION_TOKEN'),
            'expires' => '',
        ],
    ],
];
