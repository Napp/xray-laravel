<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Config\HttpSegmentConfig;
use Napp\Xray\Config\SegmentConfig;
use Napp\Xray\Xray;
use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Segment;

class XrayTest extends TestCase
{
    public function test_should_pass_all_functions()
    {
        $collector = $this->createMock(SegmentCollector::class);
        $segment = new Segment();

        $xray = new Xray($collector);

        $collector->expects($this->once())->method('tracer');
        $collector->expects($this->once())->method('getCurrentSegment');
        $collector->expects($this->once())->method('endCurrentSegment');
        $collector->expects($this->once())->method('isEnabled');
        $collector->expects($this->once())->method('addSegment');
        $collector->expects($this->once())->method('addCustomSegment')->with($segment, $this->anything());
        $collector->expects($this->once())->method('addHttpSegment')->with($this->anything());

        $xray->tracer();
        $xray->getCurrentSegment();
        $xray->endCurrentSegment();
        $xray->isEnabled();
        $xray->addSegment();
        $xray->addCustomSegment($segment, new SegmentConfig([
            SegmentConfig::NAME        => 'name',
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
    }
}
