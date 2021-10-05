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

    public function current(): Segment
    {
        return $this->collector->current();
    }

    public function isEnabled(): bool
    {
        return $this->collector->isTracerEnabled();
    }

    /**
     * @param SegmentConfig|string|null $configOrName
     * @return Segment
     */
    public function addSegment($config = null): Segment
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

    /**
     * @param string $name
     * @return Segment[]
     */
    public function getSegmentByName(string $name): array
    {
        return $this->collector->getSegmentByName($name);
    }

    public function getSegmentById(string $id): ?Segment
    {
        return $this->collector->getSegmentById($id);
    }

    public function endSegmentByName(string $name): void
    {
        $this->collector->endSegmentByName($name);
    }

    public function endSegmentById(string $id): void
    {
        $this->collector->endSegmentById($id);
    }

    public function endSegment(Segment $segment): void
    {
        $this->collector->endSegment($segment);
    }

    public function nameExist(string $name): bool
    {
        return $this->collector->nameExist($name);
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
