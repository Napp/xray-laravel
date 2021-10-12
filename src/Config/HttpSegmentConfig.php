<?php

declare(strict_types=1);

namespace Napp\Xray\Config;

use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\HttpSegment;

class HttpSegmentConfig extends SegmentConfig
{
    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var string
     */
    protected $method;

    public function __construct(
        ?string $name = null,
        ?string $url = null,
        string $method = 'get'
    ) {
        parent::__construct($name);
        $this->url = $url;
        $this->method = $method;
    }

    public function applyTo(Segment $segment)
    {
        parent::applyTo($segment);

        $this->applyToHttp($segment);
    }

    protected function applyToHttp(HttpSegment $segment)
    {
        if (isset($this->url)) {
            $segment->setUrl($this->url);
        }
        $segment->setMethod($this->method);
    }
}
