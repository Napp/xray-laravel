<?php

declare(strict_types=1);

namespace Napp\Xray\Config;

use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\HttpSegment;

class HttpSegmentConfig extends SegmentConfig
{
    const URL = 'url';

    const METHOD = 'method';

    public function applyTo(Segment $segment)
    {
        parent::applyTo($segment);

        $this->applyToHttp($segment);
    }

    protected function applyToHttp(HttpSegment $segment)
    {
        if (isset($this->config[HttpSegmentConfig::URL])) {
            $segment->setUrl($this->config[HttpSegmentConfig::URL]);
        }
        if (isset($this->config[HttpSegmentConfig::METHOD])) {
            $segment->setMethod($this->config[HttpSegmentConfig::METHOD]);
        }
    }
}
