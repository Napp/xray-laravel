<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Napp\Xray\Segments\Trace;
use Pkerrigan\Xray\Segment;

class SegmentCollector
{
    use Backtracer;

    /**
     * Segments indexed by ids.
     *
     * @var array<string, Segment>
     */
    protected $segments = [];

    public function tracer(): Trace
    {
        return Trace::getInstance();
    }

    public function getCurrentSegment(): Segment
    {
        return $this->getLastSegment() ?? $this->tracer();
    }

    /**
     * @todo Use array_key_last() instead as of PHP 7.3.
     */ 
    private function getLastSegment(): ?Segment
    {
        end($this->segments);
        $lastSegment = current($this->segments);
        reset($this->segments);
        return $lastSegment === false ? null : $lastSegment;
    }

    public function isEnabled(): bool
    {
        return (bool) config('xray.enabled');
    }

    public function getRouteFilters(): array
    {
        return (array) config('xray.route_filters');
    }

    public function getSampleRate(): int
    {
        return (int) config('xray.sample_rate');
    }

    public function initHttpTracer(Request $request): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->segments = [];
        $tracer = $this->tracer()
            ->setTraceHeader($_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? null)
            ->setName(config('xray.name') ?? config('app.name'))
            ->setClientIpAddress($request->ip())
            ->setUrl($request->url())
            ->setUserAgent(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300))
            ->setMethod($request->method());

        $tracer->begin($this->getSampleRate());

        if (in_array($request->path(), $this->getRouteFilters(), true)) {
            $tracer->setSampled(false);
        }
    }

    public function initCliTracer(string $name): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->segments = [];
        $tracer = $this->tracer()
            ->setName((config('xray.name') ?? config('app.name')) . ' CLI')
            ->setUrl($name);

        $tracer->begin($this->getSampleRate());
    }

    public function addSegment(Segment $segment, string $parentId = ''): Segment
    {
        $parent = $this->getSegment($parentId) ?? $this->getCurrentSegment();
        $parent->addSubsegment($segment);

        $segment->begin();
        return $this->segments[$segment->getId()] = $segment;
    }

    public function getSegment(string $id): ?Segment
    {
        if ($id === $this->tracer()->getId()) {
            return $this->tracer();
        }

        return $this->hasAddedSegment($id) ? $this->segments[$id] : null;
    }

    public function endSegment(string $id): void
    {
        if ($this->hasAddedSegment($id)) {
            $this->segments[$id]->end();

            unset($this->segments[$id]);
        }
    }

    public function hasAddedSegment(string $id): bool
    {
        return array_key_exists($id, $this->segments);
    }

    public function endCurrentSegment(): void
    {
        $this->getCurrentSegment()->end();
    }

    public function submitHttpTracer($response): void
    {
        $submitterClass = config('xray.submitter');
        $tracer = $this->tracer();

        if (app()->bound(Auth::class) && Auth::check()) {
            $tracer->setUser((string) Auth::user()->getAuthIdentifier());
        }

        $statusCode = $response->getStatusCode();
        $tracer->end()
            ->setResponseCode($statusCode)
            ->setError($statusCode >= 400 && $statusCode < 500)
            ->setFault($statusCode >= 500 && $statusCode < 600)
            ->submit(new $submitterClass());
    }

    public function submitCliTracer(): void
    {
        $submitterClass = config('xray.submitter');
        $tracer = $this->tracer();

        if (app()->bound(Auth::class) && Auth::check()) {
            $tracer->setUser((string) Auth::user()->getAuthIdentifier());
        }
        $tracer->end()->submit(new $submitterClass());

        $tracer::flush();
    }

    /**
     * Record and log the exception
     *
     * If [ignore_error] is set to false, it will rethrow the exception
     *
     * @throws \Exception iif set [ignore_error] to false
     *
     * @param \Exception $e
     * @return void
     */
    public function handleException(\Exception $e)
    {
        Log::warning($e->getMessage(), ['exception' => $e]);
        $this->getCurrentSegment()
            ->addAnnotation('xrayError', $e->getMessage())
            ->addMetadata('xrayDatabaseQueryTrace', $e->getTraceAsString());

        if (!config('xray.ignore_error')) {
            throw $e;
        }
    }
}
