<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Pkerrigan\Xray\Segment;

class FrameworkCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        $segment = (new Segment())->setName('laravel boot');
        $segmentId = $this->addSegment($segment)->getId();

        $this->app->booted(function () use ($segmentId) {
            $this->endSegment($segmentId);
        });
    }
}
