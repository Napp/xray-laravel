<?php

declare(strict_types=1);

namespace Napp\Xray\Facades;

use Illuminate\Support\Facades\Facade;
use Napp\Xray\Segments\Trace;
use Pkerrigan\Xray\Segment;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method static Trace tracer()
 * @method static Segment getCurrentSegment()
 * @method static bool isEnabled()
 * @method static Segment addSegment(Segment $segment, string $parentId = '')
 * @method static ?Segment getSegment(string $id)
 * @method static void endSegment(string $id)
 * @method static bool hasAddedSegment(string $id)
 * @method static void endCurrentSegment()
 * @method static void initHttpTracer(Request $request)
 * @method static void initCliTracer(string $name)
 * @method static void submitHttpTracer($response)
 * @method static void submitCliTracer()
 *
 * @see \Napp\Xray\Xray
 */
class Xray extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'xray';
    }
}
