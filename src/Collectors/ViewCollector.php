<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Napp\Xray\Config\SegmentConfig;

class ViewCollector extends EventsCollector
{
    /** @var Segment  */
    private $segment;

    public function registerEventListeners(): void
    {
        $this->app['events']->listen('creating:*', function ($view, $data = []) {
            $viewName = substr($view, 10);
            $this->segment = $this
                ->addSegment(new SegmentConfig('View ' . $viewName))
                ->end();
        });

        $this->app['events']->listen('composing:*', function ($view, $data = []) {
            $this->segment->end();
        });
    }
}
