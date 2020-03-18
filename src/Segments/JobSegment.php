<?php

declare(strict_types=1);

namespace Napp\Xray\Segments;

use Pkerrigan\Xray\Segment;

class JobSegment extends Segment
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * @var bool
     */
    private $result;

    public function setPayload(array $payload)
    {
        $this->payload = $payload;

        return $this;
    }

    public function setResult(bool $result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['job'] = array_filter([
            'payload' => $this->payload,
            'result' => $this->result ? 'success' : 'failed',
        ]);

        return $data;
    }
}
