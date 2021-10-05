<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Napp\Xray\Config\HttpSegmentConfig;
use Napp\Xray\Config\SegmentConfig;
use Napp\Xray\Segments\NamedSegment;
use Napp\Xray\Segments\TimeSegment;
use Napp\Xray\Segments\Trace;
use Pkerrigan\Xray\HttpSegment;
use Pkerrigan\Xray\Segment;

class SegmentCollector
{
    use Backtracer;

    /** @var NamedSegment[] */
    protected $segments = [];

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

    public function addSegment(?SegmentConfig $config): Segment
    {
        return $this->addCustomSegment(new TimeSegment(), $config);
    }

    public function addHttpSegment(?HttpSegmentConfig $config): HttpSegment
    {
        return $this->addCustomSegment(new HttpSegment(), $config);
    }

    public function addCustomSegment(Segment $segment, ?SegmentConfig $config): Segment
    {
        $config->applyTo($segment);

        $parent = $config->getParentSegment() ?? $this->current();
        $parent->addSubsegment($segment);

        $segment->begin($config->getStartTime());

        $this->segments[$segment->getId()] = new NamedSegment(
            $segment,
            $config->getName()
        );

        return $segment;
    }

    /**
     * Get all segments with same name.
     *
     * @param string $name
     * @return Segment[]
     */
    public function getSegmentByName(string $name): array
    {
        $result = [];

        foreach ($this->segments as $key => $segment) {
            if ($name === $segment->getName()) {
                $result[] = $segment->getSegment();
            }
        }

        return $result;
    }

    /**
     * Get specific segment by ID
     *
     * @param string $id
     * @return Segment|null
     */
    public function getSegmentById(string $id): ?Segment
    {
        return \array_key_exists($id, $this->segments)
            ? $this->segments[$id]->getSegment()
            : null;
    }

    public function endSegmentByName(string $name): void
    {
        foreach ($this->getSegmentByName($name) as $segment) {
            $this->dropSegment($segment);
        }
    }

    public function endSegmentById(string $id): void
    {
        $segment = $this->getSegmentById($id);
        if (!is_null($segment)) {
            $this->dropSegment($segment);
        }
    }

    public function nameExist(string $name): bool
    {
        foreach ($this->segments as $key => $segment) {
            if ($name === $segment->name) {
                return true;
            }
        }
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
        $this->current()
            ->addAnnotation('xrayError', $e->getMessage())
            ->addMetadata('xrayDatabaseQueryTrace', $e->getTraceAsString());
        if (!config('xray.ignore_error')) {
            throw $e;
        }
    }

    protected function dropSegment(Segment $segment)
    {
        $segment->end();

        unset($this->segments[$segment->getId()]);
    }
}
