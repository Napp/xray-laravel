<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

class ViewCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        $this->app['events']->listen('creating:*', function ($view, $data = []) {
            $viewName = substr($view, 10);
            $this->addSegment('View ' . $viewName)->end();
        });

        $this->app['events']->listen('composing:*', function ($view, $data = []) {
            $viewName = substr($view, 11);
            $this->endSegment('View ' . $viewName);
        });
    }
}
