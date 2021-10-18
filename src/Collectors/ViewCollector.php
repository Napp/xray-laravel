<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Pkerrigan\Xray\Segment;

class ViewCollector extends EventsCollector
{
    /**
     * @var string
     */
    private $segmentId;

    public function registerEventListeners(): void
    {
        $this->app['events']->listen('creating:*', function ($view, $data = []) {
            $viewName = substr($view, 10);
            $segment = (new Segment())->setName("View $viewName");
            $this->segmentId = $this->addSegment($segment)->end()->getId();
        });

        $this->app['events']->listen('composing:*', function ($view, $data = []) {
            $this->endSegment($this->segmentId);
        });
    }
}
