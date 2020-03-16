<?php

declare(strict_types=1);

namespace Napp\Xray\Segments;

use Pkerrigan\Xray\Segment;

class TimeSegment extends Segment
{
    public function begin(?float $time = null)
    {
        $this->startTime = $time ?? microtime(true);

        return $this;
    }
}
