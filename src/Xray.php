<?php

declare(strict_types=1);

namespace Napp\Xray;

use Napp\Xray\Collectors\SegmentCollector;
use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\Trace;
use Symfony\Component\HttpFoundation\Request;

class Xray
{
    private $collector;

    private $requestFilterCallables;

    public function __construct(SegmentCollector $collector)
    {
        $this->collector = $collector;
        $this->requestFilterCallables = [];
    }

    public function tracer(): Trace
    {
        return $this->collector->tracer();
    }

    public function current(): Segment
    {
        return $this->collector->current();
    }

    public function isEnabled(): bool
    {
        return $this->collector->isTracerEnabled();
    }

    public function addSegment(string $name, ?float $startTime = null, ?array $metadata = null): Segment
    {
        return $this->collector->addSegment($name, $startTime, $metadata);
    }

    public function addCustomSegment(Segment $segment, string $name): Segment
    {
        return $this->collector->addCustomSegment($segment, $name);
    }

    public function getSegment(string $name): ?Segment
    {
        return $this->collector->getSegment($name);
    }

    public function endSegment(string $name): void
    {
        $this->collector->endSegment($name);
    }

    public function hasAddedSegment(string $name): bool
    {
        return $this->collector->hasAddedSegment($name);
    }

    public function endCurrentSegment(): void
    {
        $this->collector->endCurrentSegment();
    }

    public function initHttpTracer(Request $request): void
    {
        $this->collector->initHttpTracer($request);
    }

    public function initCliTracer(string $name): void
    {
        $this->collector->initCliTracer($name);
    }

    public function submitHttpTracer($response): void
    {
        $this->collector->submitHttpTracer($response);
    }

    public function submitCliTracer(): void
    {
        $this->collector->submitCliTracer();
    }

    public function addRequestFilterCallback(callable $callback): void
    {
        $this->requestFilterCallables[] = $callback;
    }

    public function shouldCaptureRequest(Request $request): bool
    {
        foreach($this->requestFilterCallables as $requestFilterCallable) {
            if(!$requestFilterCallable($request)) {
                return false;
            }
        }

        return true;
    }
}
