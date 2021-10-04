<?php

declare(strict_types=1);

namespace Napp\Xray\Segments;

use Pkerrigan\Xray\Segment;

class SegmentWithPublicName
{
    /** @var Segment */
    public $segment;

    /** @var string */
    public $name;

    public function __construct(Segment $segment, string $name) {
        $this->segment = $segment;
        $this->name = $name;
    }
}
