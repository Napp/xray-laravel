<?php

declare(strict_types=1);

namespace Napp\Xray;

use Illuminate\Http\Request;
use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Segments\HttpSegment;
use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\Trace;

class Xray
{
    /** @var SegmentCollector */
    private $collector;

    public function __construct(SegmentCollector $collector)
    {
        $this->collector = $collector;
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

    /**
     * Add HTTP segment
     *
     * If name is not set, it will use url
     *
     * $config default values:
     * [
     *   "method": "GET",
     *   "name": null,
     * ]
     *
     * @param string $name
     * @param array|null $config
     * @return Segment
     */
    public function addHttpSegment(string $url, ?array $config = []): HttpSegment
    {
        return $this->collector->addHttpSegment($url, $config);
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

    public function initCliTracer(String $name): void
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
}
