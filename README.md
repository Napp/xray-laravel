# Aws X-Ray for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/napp/xray-laravel.svg?style=flat-square)](https://packagist.org/packages/napp/xray-laravel)

The package automatically trace your laravel application and sends to AWS X-Ray.

## Installation

You can install the package via composer:

```bash
composer require napp/xray-laravel
```

If you want to disable the Tracer, just add to `.env`

```dotenv
XRAY_ENABLED=false
```

## What Tracing is supported

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
