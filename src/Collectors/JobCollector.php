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
    /** @var JobSegment  */
    private $segment;

    public function registerEventListeners(): void
    {
        $this->app['events']->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->initCliTracer($event->job->resolveName());

            $this->segment = $this->addCustomSegment(
                (new JobSegment())->setPayload($event->job->payload()),
                new SegmentConfig($event->job->resolveName())
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

    public function handleJobEnded(Job $job, bool $success = false): void
    {
        $this->segment
            ->setError($success)
            ->end();

        $this->submitCliTracer();
    }
}
