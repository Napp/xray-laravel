<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Napp\Xray\Config\SegmentConfig;

class FrameworkCollector extends EventsCollector
{
    /** @var Segment  */
    protected $segment;

    public function registerEventListeners(): void
    {
        $this->segment = $this->addSegment(new SegmentConfig('laravel boot'));

        $this->app->booted(function () {
            $this->segment->end();
        });
    }
}
