<?php

declare(strict_types=1);

namespace Napp\Xray;

use Illuminate\Http\Request;
use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Config\HttpSegmentConfig;
use Napp\Xray\Config\SegmentConfig;
use Napp\Xray\Segments\Trace;
use Pkerrigan\Xray\HttpSegment;
use Pkerrigan\Xray\Segment;

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

    public function getCurrentSegment(): Segment
    {
        return $this->collector->getCurrentSegment();
    }

    public function isEnabled(): bool
    {
        return $this->collector->isEnabled();
    }

    public function addSegment(?SegmentConfig $config = null): Segment
    {
        return $this->collector->addSegment($config);
    }

    public function addHttpSegment(?HttpSegmentConfig $config = null): HttpSegment
    {
        return $this->collector->addHttpSegment($config);
    }

    public function addCustomSegment(Segment $segment, ?SegmentConfig $config = null): Segment
    {
        return $this->collector->addCustomSegment($segment, $config);
    }

    public function getSegment(string $id): ?Segment
    {
        return $this->collector->getSegment($id);
    }

    public function endSegment(string $id): void
    {
        $this->collector->endSegment($id);
    }

    public function hasAddedSegment(string $id): bool
    {
        return $this->collector->hasAddedSegment($id);
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
