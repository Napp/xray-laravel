<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Xray;
use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Segment;

class XrayTest extends TestCase
{
    public function testShouldPassAllFunctions()
    {
        $collector = $this->createMock(SegmentCollector::class);
        $segment = new Segment();

        $xray = new Xray($collector);

        $collector->expects($this->once())->method('tracer');
        $collector->expects($this->once())->method('getCurrentSegment');
        $collector->expects($this->once())->method('endCurrentSegment');
        $collector->expects($this->once())->method('isEnabled');
        $collector->expects($this->once())->method('addSegment')->with($segment);
        $collector->expects($this->once())->method('hasAddedSegment')->with($segment->getId());
        $collector->expects($this->once())->method('getSegment')->with($segment->getId());
        $collector->expects($this->once())->method('endSegment')->with($segment->getId());

        $xray->tracer();
        $xray->getCurrentSegment();
        $xray->endCurrentSegment();
        $xray->isEnabled();
        $xray->addSegment($segment);
        $xray->hasAddedSegment($segment->getId());
        $xray->getSegment($segment->getId());
        $xray->endSegment($segment->getId());
    }
}
