<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

class FrameworkCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        // Application and Laravel startup times
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        $this->addSegment('composer autoload', $startTime);

        $this->app->booting(function () {
            $this->addSegment('laravel boot');
            $this->endSegment('composer autoload');
        });

        $this->app->booted(function () {
            // avoid already booted, it will miss end time
            // $this->endSegment('composer autoload');
            $this->endSegment('laravel boot');
        });
    }
}
