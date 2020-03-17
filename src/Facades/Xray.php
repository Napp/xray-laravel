<?php

declare(strict_types=1);

namespace Napp\Xray\Facades;

use Illuminate\Support\Facades\Facade;
use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\Trace;
use Symfony\Component\HttpFoundation\Request;

class Xray extends Facade
{
    /**
     * @method static Trace tracer()
     * @method static Segment current()
     * @method static bool isEnabled()
     * @method static Segment addSegment(string $name, ?float $startTime = null, ?array $metadata = null)
     * @method static Segment addCustomSegment(Segment $segment, string $name)
     * @method static null|Segment getSegment(string $name)
     * @method static void endSegment(string $name)
     * @method static bool hasAddedSegment(string $name)
     * @method static void endCurrentSegment()
     * @method static void initHttpTracer(Request $request)
     * @method static void initCliTracer(string $name)
     * @method static void submitHttpTracer($response)
     * @method static void submitCliTracer()
     *
     * @see \Napp\Xray\Xray
     */
    protected static function getFacadeAccessor()
    {
        return 'xray';
    }
}
