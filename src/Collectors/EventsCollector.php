<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Foundation\Application;

abstract class EventsCollector extends SegmentCollector
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->registerEventListeners();
    }

    abstract public function registerEventListeners(): void;
}
