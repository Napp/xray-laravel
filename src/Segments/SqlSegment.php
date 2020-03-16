<?php

declare(strict_types=1);

namespace Napp\Xray\Segments;

use Pkerrigan\Xray\SqlSegment as BaseSegment;

class SqlSegment extends BaseSegment
{
    public function end($timeSpend = null)
    {
        $this->endTime = $timeSpend === null ? microtime(true) : $this->startTime + $timeSpend;

        return $this;
    }
}
