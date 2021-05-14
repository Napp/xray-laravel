<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Napp\Xray\Segments\JobSegment;

class JobCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        $this->app['events']->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->initCliTracer($event->job->resolveName());

            $this->addCustomSegment(
                (new JobSegment())->setName($event->job->resolveName())->setPayload($event->job->payload()),
                $this->getJobId($event->job)
            );
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
        if ($this->hasAddedSegment($this->getJobId($job))) {
            $this->getSegment($this->getJobId($job))->setError($success);
            $this->endSegment($this->getJobId($job));
            $this->submitCliTracer();
        }
    }
}
