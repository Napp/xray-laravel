<?php

declare(strict_types=1);

namespace Napp\Xray\Facades;

use Illuminate\Support\Facades\Facade;
use Napp\Xray\Config\HttpSegmentConfig;
use Napp\Xray\Config\SegmentConfig;
use Napp\Xray\Segments\Trace;
use Pkerrigan\Xray\HttpSegment;
use Pkerrigan\Xray\Segment;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method static Trace tracer()
 * @method static Segment getCurrentSegment()
 * @method static bool isEnabled()
 * @method static Segment addSegment(?SegmentConfig $config = null)
 * @method static HttpSegment addHttpSegment(?HttpSegmentConfig $config = null)
 * @method static Segment addCustomSegment(Segment $segment, ?SegmentConfig $config = null)
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
