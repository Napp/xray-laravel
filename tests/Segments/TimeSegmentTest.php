<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Napp\Xray\Segments\TimeSegment;
use PHPUnit\Framework\TestCase;

class TimeSegmentTest extends TestCase
{
    public function test_setting_end_time()
    {
        $segment = new TimeSegment();
        $segment->setName('test test 123')
            ->begin(1584448767.5)
            ->end();

        $serialized = $segment->jsonSerialize();
        $this->assertEquals(1584448767.5, $serialized['start_time']);
    }
}
