<?php

declare(strict_types=1);

namespace Napp\Xray\Submission;

use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\Submission\DaemonSegmentSubmitter as SubmissionDaemonSegmentSubmitter;
use Pkerrigan\Xray\Submission\SegmentSubmitter;

class DaemonSegmentSubmitter implements SegmentSubmitter
{
    /**
     * @var DaemonSegmentSubmitterWithLog
     */
    private $submitter;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    public function __construct()
    {
        $this->host = config('xray.daemon_host');
        $this->port = (int) config('xray.daemon_port');
    }

    /**
     * Get or create the Daemon submitter.
     *
     * @return DaemonSegmentSubmitterWithLog
     */
    protected function submitter(): SubmissionDaemonSegmentSubmitter
    {
        if (is_null($this->submitter)) {
            $this->submitter = new SubmissionDaemonSegmentSubmitter(
                $this->host,
                $this->port
            );
        }

        return $this->submitter;
    }

    /**
     * @param Segment $segment
     * @return void
     */
    public function submitSegment(Segment $segment)
    {
        $this->submitter()->submitSegment($segment);
    }
}
