<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Napp\Xray\Xray;

class FrameworkCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        $this->addSegment('laravel boot');

        $this->app->booted(function () {
            $this->endSegmentByName('laravel boot');
        });
    }
}
