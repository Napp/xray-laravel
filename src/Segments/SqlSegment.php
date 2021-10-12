<?php

declare(strict_types=1);

namespace Napp\Xray\Segments;

use Pkerrigan\Xray\SqlSegment as BaseSegment;

class SqlSegment extends BaseSegment
{
    public function end(?float $timeSpend = null)
    {
        if (is_null($timeSpend)) {
            $this->endTime = microtime(true);
        } else {
            // when we know time spent, it should finish query, need to swap start/end time
            $this->endTime = $this->startTime;
            $this->startTime = $this->startTime - $timeSpend;
        }

        return $this;
    }
}
