<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

class ViewCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        $this->app['events']->listen('creating:*', function ($view, $data = [])  {
            $this->addSegment('View ' . $view)->end();
        });

        $this->app['events']->listen('composing:*', function ($view, $data = [])  {
            if ($this->hasAddedSegment('View ' . $view)) {
                $this->endSegment('View ' . $view);
            }
        });
    }
}
