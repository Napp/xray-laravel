<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Segments\TimeSegment;
use Napp\Xray\Xray;
use PHPUnit\Framework\TestCase;

class XrayTest extends TestCase
{
    public function test_setting_end_time()
    {
        $collector = $this->createMock(SegmentCollector::class);
        $segment = new TimeSegment();

        $xray = new Xray($collector);

        $collector->expects($this->once())->method('tracer');
        $collector->expects($this->once())->method('current');
        $collector->expects($this->once())->method('isTracerEnabled');
        $collector->expects($this->once())->method('addSegment')->with('name', null, null);
        $collector->expects($this->once())->method('addCustomSegment')->with($segment, 'name');
        $collector->expects($this->once())->method('addHttpSegment')->with('url', []);
        $collector->expects($this->once())->method('getSegment')->with('name');
        $collector->expects($this->once())->method('endSegment')->with('name');
        $collector->expects($this->once())->method('hasAddedSegment')->with('name');
        $collector->expects($this->once())->method('endCurrentSegment');

        $xray->tracer();
        $xray->current();
        $xray->isEnabled();
        $xray->addSegment('name');
        $xray->addCustomSegment($segment, 'name');
        $xray->addHttpSegment('url');
        $xray->getSegment('name');
        $xray->endSegment('name');
        $xray->hasAddedSegment('name');
        $xray->endCurrentSegment();
    }
}
