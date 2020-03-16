<?php

declare(strict_types=1);

namespace Napp\Xray;

use Napp\Xray\Collectors\SegmentCollector;
use Symfony\Component\HttpFoundation\Request;

class Xray
{
    private $collector;

    public function __construct(SegmentCollector $collector)
    {
        $this->collector = $collector;
    }

    public function isEnabled(): bool
    {
        return $this->collector->isTracerEnabled();
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
}
