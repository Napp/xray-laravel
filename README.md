# Aws X-Ray for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/napp/xray-laravel.svg?style=flat-square)](https://packagist.org/packages/napp/xray-laravel)

The package automatically trace your laravel application and sends to AWS X-Ray.

![Activate](https://raw.githubusercontent.com/Napp/xray-laravel/master/docs/xray-timeline.png)

You can even inspect your DB query stack trace

![Activate](https://raw.githubusercontent.com/Napp/xray-laravel/master/docs/lambda-db-stack.png)

## Installation

1. Install the package via composer:

```bash
composer require napp/xray-laravel
```

2. Add middleware to the top of the global middleware in `App\Http\Kernel.php`

```php
protected $middleware = [
    \Napp\Xray\Middleware\RequestTracing::class, // here

    \App\Http\Middleware\TrustProxies::class,
    \App\Http\Middleware\CheckForMaintenanceMode::class,
    // ...
];
```

3. Add XrayServiceProvider to the very top of providers in `config/app.php`. 

```php
'providers' => [
    /*
     * Laravel Framework Service Providers...
     */
    Napp\Xray\XrayServiceProvider::class, // here
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    // ...
];
```

4. Head over to AWS Console, to Lambda and find your function. Activate X-Ray Tracing.

![Activate](https://raw.githubusercontent.com/Napp/xray-laravel/master/docs/lambda-enable-xray.png)


## Disable Tracer

If you want to disable the Tracer, just add to `.env`

```dotenv
XRAY_ENABLED=false
```


## What Tracers are supported

- [x] Composer autoload
- [x] Framework boot
- [x] Route matching
- [x] HTTP requests
- [x] Database queries
- [x] Queue jobs

## Not supported yet

- [ ] Exceptions
- [ ] Blade render


## LICENSE

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
