<?php

declare(strict_types=1);

namespace Napp\Xray\Submission;

use Illuminate\Support\Facades\Log;
use Pkerrigan\Xray\Segment;
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
    protected function submitter(): DaemonSegmentSubmitterWithLog
    {
        if (is_null($this->submitter)) {
            $this->submitter = new DaemonSegmentSubmitterWithLog(
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
        Log::channel('trace-log')->debug('xray send start');
        $this->submitter()->submitSegment($segment);
        Log::channel('trace-log')->debug('xray send end');
    }
}

class DaemonSegmentSubmitterWithLog
{
    const MAX_SEGMENT_SIZE = 64000;

    const HEADER = [
        'format' => 'json',
        'version' => 1
    ];

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var \Socket
     */
    private $socket;

    public function __construct(string $host = '127.0.0.1', int $port = 2000)
    {
        $this->host = $host;
        $this->port = $port;
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    public function __destruct()
    {
        socket_close($this->socket);
    }

    /**
     * @param Segment $segment
     * @return void
     */
    public function submitSegment(Segment $segment)
    {
        $packet = $this->buildPacket($segment);
        $packetLength = strlen($packet);

        if ($packetLength > self::MAX_SEGMENT_SIZE) {
            $this->submitFragmented($segment);
            return;
        }

        $result = $this->sendPacket($packet);
        Log::channel('trace-log')->debug('xray send by submitSegment', ['result' => $result, 'packet' => $packet]);
    }

    /**
     * @param Segment|array $segment
     * @return string
     */
    private function buildPacket($segment): string
    {
        return implode("\n", array_map('json_encode', [self::HEADER, $segment]));
    }

    /**
     * @param string $packet
     * @return void
     */
    private function sendPacket(string $packet)
    {
        socket_sendto($this->socket, $packet, strlen($packet), 0, $this->host, $this->port);
    }

    /**
     * @param Segment $segment
     * @return void
     */
    private function submitFragmented(Segment $segment)
    {
        $rawSegment = $segment->jsonSerialize();
        /** @var Segment[] $subsegments */
        $subsegments = $rawSegment['subsegments'] ?? [];
        unset($rawSegment['subsegments']);
        $this->submitOpenSegment($rawSegment);

        foreach ($subsegments as $subsegment) {
            $subsegment = clone $subsegment;
            $subsegment->setParentId($segment->getId())
                       ->setTraceId($segment->getTraceId())
                       ->setIndependent(true);
            $this->submitSegment($subsegment);
        }

        $completePacket = $this->buildPacket($rawSegment);
        $result = $this->sendPacket($completePacket);
        Log::channel('trace-log')->debug('xray send by submitFragmented', ['result' => $result, 'packet' => $completePacket]);
    }

    /**
     * @param $rawSegment
     * @return void
     */
    private function submitOpenSegment(array $openSegment)
    {
        unset($openSegment['end_time']);
        $openSegment['in_progress'] = true;
        $initialPacket = $this->buildPacket($openSegment);
        $this->sendPacket($initialPacket);
    }
}
