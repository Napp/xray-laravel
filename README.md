# Aws X-Ray for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/napp/xray-laravel.svg?style=flat-square)](https://packagist.org/packages/napp/xray-laravel)

The package automatically trace your laravel application and sends to AWS X-Ray.

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

## Disable Tracer

If you want to disable the Tracer, just add to `.env`

```dotenv
XRAY_ENABLED=false
```


## What Tracers are supported

- [x] Composer autoload
- [x] Framework boot
- [x] HTTP requests
- [x] Database queries
- [x] Queue jobs

## Not supported yet

- [ ] Exceptions
- [ ] Blade render


## LICENSE

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
