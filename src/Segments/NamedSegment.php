<?php

declare(strict_types=1);

namespace Napp\Xray\Segments;

use Pkerrigan\Xray\Segment;

class NamedSegment
{
    /** @var Segment */
    protected $segment;

    /** @var string|null */
    protected $name;

    public function __construct(Segment $segment, ?string $name) {
        $this->segment = $segment;
        $this->name = $name;
    }

    public function getSegment(): Segment
    {
        return $this->segment;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
