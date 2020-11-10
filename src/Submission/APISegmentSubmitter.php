<?php

declare(strict_types=1);

namespace Napp\Xray\Submission;

use Aws\XRay\XRayClient;
use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\Submission\SegmentSubmitter;

class APISegmentSubmitter implements SegmentSubmitter
{
    /**
     * @var \Aws\XRay\XRayClient
     */
    private $client;

    public function __construct()
    {
        $this->client = new XRayClient(config('xray.aws'));
    }

    public function submitSegment(Segment $segment)
    {
        $this->client->putTraceSegments([
            'TraceSegmentDocuments' => [
                json_encode($segment->jsonSerialize()),
            ],
        ]);
    }
}
