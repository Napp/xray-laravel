<?php

declare(strict_types=1);

namespace Napp\Xray\Segments;

use Pkerrigan\Xray\SqlSegment as BaseSegment;

class SqlSegment extends BaseSegment
{
    public function begin(?float $startTime = null)
    {
        $this->startTime = $startTime ?? microtime(true);

        return $this;
    }

    public function end(?float $timeSpend = null)
    {
        $this->endTime = $timeSpend === null ? microtime(true) : $this->startTime + $timeSpend;

        return $this;
    }
}
