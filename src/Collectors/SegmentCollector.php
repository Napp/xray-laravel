<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Napp\Xray\Segments\HttpSegment;
use Napp\Xray\Segments\TimeSegment;
use Napp\Xray\Segments\Trace;
use Pkerrigan\Xray\Segment;

class SegmentCollector
{
    use Backtracer;

    /** @var array */
    protected $segments;

    public function tracer(): Trace
    {
        return Trace::getInstance();
    }

    public function current(): Segment
    {
        return $this->tracer()->getCurrentSegment();
    }

    public function isTracerEnabled(): bool
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
        if (!$this->isTracerEnabled()) {
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
        if (!$this->isTracerEnabled()) {
            return;
        }

        $this->segments = [];
        $tracer = $this->tracer()
            ->setName((config('xray.name') ?? config('app.name')) . ' CLI')
            ->setUrl($name);

        $tracer->begin($this->getSampleRate());
    }

    public function addSegment(string $name, ?float $startTime = null, ?array $metadata = null): Segment
    {
        $segment = (new TimeSegment())->setName($name);

        if (null !== $metadata) {
            $segment->addMetadata('info', $metadata);
        }

        $this->current()->addSubsegment($segment);
        $segment->begin($startTime);
        $this->segments[$name] = $segment;

        return $segment;
    }

    public function addCustomSegment(Segment $segment, string $name): Segment
    {
        $this->current()->addSubsegment($segment);
        $segment->begin();
        $this->segments[$name] = $segment;

        return $segment;
    }

    /**
     * Add HTTP segment
     *
     * $config default values:
     * [
     *   "method": "GET",
     *   "contentLength": null,
     * ]
     *
     * @param string $name
     * @param string $url
     * @param string|null $method = "GET"
     * @return Segment
     */
    public function addHttpSegment(string $name, string $url, ?string $method = 'GET'): Segment
    {
        $segment = (new HttpSegment())->setName($name);

        $segment->setMethod($method);
        $segment->setUrl($url);

        $this->current()->addSubsegment($segment);
        $segment->begin();
        $this->segments[$name] = $segment;

        return $segment;
    }

    public function getSegment(string $name): ?Segment
    {
        if ($this->hasAddedSegment($name)) {
            return $this->segments[$name];
        }

        return null;
    }

    public function endSegment(string $name): void
    {
        if ($this->hasAddedSegment($name)) {
            $this->segments[$name]->end();

            unset($this->segments[$name]);
        }
    }

    /**
     * End HTTP segment by segment name
     *
     * @param string $name
     * @param integer|null $responseCode = 200
     * @return void
     */
    public function endHttpSegment(string $name, ?int $responseCode = 200): void
    {
        if ($this->hasAddedSegment($name)) {
            if ($this->segments[$name] instanceof HttpSegment) {
                $this->segments[$name]->setResponseCode($responseCode);
            }
            $this->segments[$name]->end();

            unset($this->segments[$name]);
        }
    }

    public function hasAddedSegment(string $name): bool
    {
        return \array_key_exists($name, $this->segments);
    }

    public function endCurrentSegment(): void
    {
        $this->current()->end();
    }

    public function submitHttpTracer($response): void
    {
        $submitterClass = config('xray.submitter');
        $tracer = $this->tracer();

        if (app()->bound(Auth::class) && Auth::check()) {
            $tracer->setUser((string) Auth::user()->getAuthIdentifier());
        }
        $tracer->end()
            ->setResponseCode($response->getStatusCode())
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
}
