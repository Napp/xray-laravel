<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Napp\Xray\Segments\JobSegment;

class JobCollector extends EventsCollector
{
    private string $segmentId;

    public function registerEventListeners(): void
    {
        $this->app['events']->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->initCliTracer($event->job->resolveName());

            $segment = (new JobSegment())
                ->setPayload($event->job->payload())
                ->setName($event->job->resolveName());
            $this->segmentId = $this->addSegment($segment)->getId();
        });

        $this->app['events']->listen(JobProcessed::class, function (JobProcessed $event) {
            $this->handleJobEnded(true);
        });

        $this->app['events']->listen(JobFailed::class, function (JobFailed $event) {
            $this->handleJobEnded(false);
        });

        $this->app['events']->listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event) {
            $this->handleJobEnded(false);
        });
    }

    private function handleJobEnded(bool $success): void
    {
        $this->getSegment($this->segmentId)->setError($success);
        $this->endSegment($this->segmentId);
        $this->submitCliTracer();
    }
}
