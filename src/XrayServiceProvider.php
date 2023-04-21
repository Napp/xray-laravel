<?php

declare(strict_types=1);

namespace Napp\Xray;

use Illuminate\Support\ServiceProvider;
use Napp\Xray\Collectors\CacheCollector;
use Napp\Xray\Collectors\DatabaseQueryCollector;
use Napp\Xray\Collectors\FrameworkCollector;
use Napp\Xray\Collectors\JobCollector;
use Napp\Xray\Collectors\RouteCollector;
use Napp\Xray\Collectors\ViewCollector;

class XrayServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/xray.php', 'xray');
        $this->registerFacade();
    }

    /**
     * Booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/xray.php' => config_path('xray.php')], 'xray-config');
        }

        if (!config('xray.enabled')) {
            return;
        }

        $this->registerCollectors();
    }

    /**
     * Register collectors and start listening for events.
     */
    protected function registerCollectors(): void
    {
        $this->registerTracing();

        if (config('xray.db_query') || $this->app->runningInConsole()) {
            app(DatabaseQueryCollector::class);
        }

        if (config('xray.job')) {
            app(JobCollector::class);
        }

        if (config('xray.view')) {
            app(ViewCollector::class);
        }

        if (config('xray.route')) {
            app(RouteCollector::class);
        }

        if (config('xray.framework')) {
            app(FrameworkCollector::class);
        }

        if (config('xray.cache')) {
            app(CacheCollector::class);
        }
    }

    /**
     * Initialize X-Ray tracing
     *
     * @return void
     */
    protected function registerTracing(): void
    {
        if (!$this->app->runningInConsole()) {
            /** @var Xray */
            $xray = $this->app->xray;
            $xray->initHttpTracer($this->app->request);
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
