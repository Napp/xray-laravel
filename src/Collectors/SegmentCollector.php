<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Support\Facades\Auth;
use Napp\Xray\Segments\TimeSegment;
use Napp\Xray\Segments\Trace;
use Pkerrigan\Xray\Segment;
use Symfony\Component\HttpFoundation\Request;

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

    public function initHttpTracer(Request $request): void
    {
        if (! $this->isTracerEnabled()) {
            return;
        }

        $this->segments = [];
        $tracer = $this->tracer()
            ->setTraceHeader($_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? null)
            ->setName(config('app.name') . ' HTTP')
            ->setClientIpAddress($request->getClientIp())
            ->addAnnotation('Framework', 'Laravel ' . app()->version())
            ->addAnnotation('PHP Version', PHP_VERSION)
            ->setUrl($request->url())
            ->setMethod($request->method());

        $tracer->begin();
    }

    public function initCliTracer(string $name): void
    {
        if (! $this->isTracerEnabled()) {
            return;
        }

        $this->segments = [];
        $tracer = $this->tracer()
            ->setName(config('app.name') . ' CLI')
            ->addAnnotation('framework', 'Laravel ' . app()->version())
            ->setUrl($name);

        $tracer->begin();
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
