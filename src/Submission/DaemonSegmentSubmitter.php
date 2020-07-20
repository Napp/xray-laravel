<?php

declare(strict_types=1);

namespace Napp\Xray\Submission;

use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\Submission\DaemonSegmentSubmitter as SubmissionDaemonSegmentSubmitter;
use Pkerrigan\Xray\Submission\SegmentSubmitter;

class DaemonSegmentSubmitter implements SegmentSubmitter
{
    /**
     * @var SubmissionDaemonSegmentSubmitter
     */
    private $submitter;

    public function __construct()
    {
        $this->submitter = new SubmissionDaemonSegmentSubmitter(
            env('_AWS_XRAY_DAEMON_ADDRESS'),
            (int) env('_AWS_XRAY_DAEMON_PORT')
        );
    }

    /**
     * @param Segment $segment
     * @return void
     */
    public function submitSegment(Segment $segment)
    {
        $this->submitter->submitSegment($segment);
    }
}
