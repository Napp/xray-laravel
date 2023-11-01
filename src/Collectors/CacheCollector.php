<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Redis\Events\CommandExecuted;

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
        $this->app->events->listen(CommandExecuted::class, function (CommandExecuted $cache) {
            $this->handleQueryReport($cache->command, 'Cache redis command executed');
        });
    }

    protected function handleQueryReport(string $cacheKey, string $eventName): void
    {
        $backtrace = $this->getBacktrace();

        $eventSuffix = sizeof($backtrace) > 0 ? ('at ' . $this->getCallerClass($backtrace)) : "(too deeply nested)";

        $this
            ->addSegment("$eventName $eventSuffix")
            ->addAnnotation('Key', $cacheKey)
            ->addMetadata('backtrace', $backtrace)
            ->end();
    }
}
