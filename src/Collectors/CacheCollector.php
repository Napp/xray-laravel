<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Pkerrigan\Xray\Segment;

class CacheCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        $this->app->events->listen(CacheHit::class, function (CacheHit $cache) {
            $this->handleQueryReport($cache->key, 'Cache hit');
        });
        $this->app->events->listen(CacheMissed::class, function (CacheMissed $cache) {
            $this->handleQueryReport($cache->key, 'Cache miss');
        });
        $this->app->events->listen(KeyWritten::class, function (KeyWritten $cache) {
            $this->handleQueryReport($cache->key, 'Cache set');
        });
        $this->app->events->listen(KeyForgotten::class, function (KeyForgotten $cache) {
            $this->handleQueryReport($cache->key, 'Cache delete');
        });
    }

    protected function handleQueryReport(string $cacheKey, string $eventName): void
    {
        $backtrace = $this->getBacktrace();
        $segment = (new Segment())->setName($eventName . ' at ' . $this->getCallerClass($backtrace));

        $this
            ->addSegment($segment)
            ->addAnnotation('Key', $cacheKey)
            ->addMetadata('backtrace', $backtrace)
            ->end();
    }
}
