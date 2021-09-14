<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

class FrameworkCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        if (! $this->app->runningInConsole()) {
            $this->initHttpTracer($this->app['request']);
        }
        // Application and Laravel startup times
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        $this->addSegment('laravel boot', $startTime);

        $this->app->booted(function () {
            $this->endSegment('laravel boot');
        });
    }
}
