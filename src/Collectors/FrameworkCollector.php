<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

class FrameworkCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        // Application and Laravel startup times
        // LARAVEL_START is defined at the entry point of the application
        // https://github.com/laravel/laravel/blob/master/public/index.php#L10
        $this->addSegment('composer autoload', LARAVEL_START);

        $this->app->booting(function () {
            $this->addSegment('laravel boot');
            $this->endSegment('composer autoload');
        });

        $this->app->booted(function () {
            $this->endSegment('laravel boot');
        });
    }
}
