<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Config\HttpSegmentConfig;
use Napp\Xray\Config\SegmentConfig;
use Napp\Xray\Segments\TimeSegment;
use Napp\Xray\Xray;
use PHPUnit\Framework\TestCase;

class XrayTest extends TestCase
{
    public function test_should_pass_all_functions()
    {
        $collector = $this->createMock(SegmentCollector::class);
        $segment = new TimeSegment();

        $xray = new Xray($collector);

        $collector->expects($this->once())->method('tracer');
        $collector->expects($this->once())->method('current');
        $collector->expects($this->once())->method('isTracerEnabled');
        $collector->expects($this->once())->method('addSegment')->with('name');
        $collector->expects($this->once())->method('addCustomSegment')->with($segment, $this->anything());
        $collector->expects($this->once())->method('addHttpSegment')->with($this->anything());
        $collector->expects($this->once())->method('getSegmentByName')->with('name');
        $collector->expects($this->once())->method('getSegmentById')->with($segment->getId());
        $collector->expects($this->once())->method('endSegmentById')->with($segment->getId());
        $collector->expects($this->once())->method('endSegmentByName')->with('name');
        $collector->expects($this->once())->method('endSegment')->with($segment);
        $collector->expects($this->once())->method('nameExist')->with('name');
        $collector->expects($this->once())->method('endCurrentSegment');

        $xray->tracer();
        $xray->current();
        $xray->isEnabled();
        $xray->addSegment('name');
        $xray->addCustomSegment($segment, new SegmentConfig([
            SegmentConfig::NAME        => 'name',
            SegmentConfig::START_TIME  => 0,
            SegmentConfig::ANNOTATIONS => [
                'key1' => 'ann1'
            ],
            SegmentConfig::METADATA    => [
                'key1' => 'meta1'
            ],
        ]));
        $xray->addHttpSegment(new HttpSegmentConfig([
            HttpSegmentConfig::NAME           => 'name',
            HttpSegmentConfig::URL            => 'url',
            HttpSegmentConfig::METHOD         => 'method',
            HttpSegmentConfig::PARENT_SEGMENT => $segment,
        ]));
        $xray->getSegmentByName('name');
        $xray->getSegmentById($segment->getId());
        $xray->endSegmentById($segment->getId());
        $xray->endSegmentByName('name');
        $xray->endSegment($segment);
        $xray->nameExist('name');
        $xray->endCurrentSegment();
    }
}
