<?php
declare(strict_types=1);

namespace Napp\Xray;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Napp\Xray\Collectors\DatabaseQueryCollector;
use Napp\Xray\Collectors\FrameworkCollector;
use Napp\Xray\Collectors\JobCollector;
use Napp\Xray\Middleware\RequestTracing;

class XrayServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/xray.php', 'xray');
        $this->registerFacade();
        if (! config('xray.enabled') || $this->app->runningInConsole() ) {
            return;
        }

        app(FrameworkCollector::class);
    }

    /**
     * Booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/xray.php' => config_path('xray.php')]);

        if (! config('xray.enabled') || $this->app->runningInConsole()) {
            return;
        }

        $this->registerMiddleware();
        $this->registerCollectors();
    }

    /**
     * Add the middleware to the very top of the list,
     * aiming to have better time measurements.
     */
    protected function registerMiddleware(): void
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(RequestTracing::class);
    }

    /**
     * Register collectors and start listening for events.
     */
    protected function registerCollectors(): void
    {
        if (config('xray.db_query')) {
            app(DatabaseQueryCollector::class);
        }

        if (config('xray.job')) {
            app(JobCollector::class);
        }
    }

    /**
     * Register facades into the Service Container.
     */
    protected function registerFacade(): void
    {
        $this->app->singleton('xray', function ($app) {
            return $app->make(Xray::class);
        });
    }

}
