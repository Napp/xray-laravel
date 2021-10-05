<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Napp\Xray\Config\SegmentConfig;
use Napp\Xray\Segments\JobSegment;

class JobCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        $this->app['events']->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->initCliTracer($event->job->resolveName());

            $segment = $this->addCustomSegment(
                (new JobSegment())->setPayload($event->job->payload()),
                new SegmentConfig([
                    SegmentConfig::NAME => $this->getJobId($event->job)
                ]));

            // override the name of job
            $segment->setName($event->job->resolveName());
        });

        $this->app['events']->listen(JobProcessed::class, function (JobProcessed $event) {
            $this->handleJobEnded($event->job, true);
        });

        $this->app['events']->listen(JobFailed::class, function (JobFailed $event) {
            $this->handleJobEnded($event->job, false);
        });

        $this->app['events']->listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event) {
            $this->handleJobEnded($event->job, false);
        });
    }

    protected function getJobId(Job $job): string
    {
        if ($jobId = $job->getJobId()) {
            return $jobId;
        }

        return sha1($job->getRawBody());
    }

    public function handleJobEnded(Job $job, bool $success = false): void
    {
        foreach ($this->getSegmentByName($this->getJobId($job)) as $segment) {
            $segment->setError($success);
            $this->endSegment($segment);
        }

        $this->submitCliTracer();
    }
}
