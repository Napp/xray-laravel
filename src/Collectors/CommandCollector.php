<?php

declare(strict_types=1);

namespace Napp\Xray\Collectors;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Napp\Xray\Segments\JobSegment;

class CommandCollector extends EventsCollector
{
    public function registerEventListeners(): void
    {
        $this->app['events']->listen(CommandStarting::class, function (CommandStarting $event) {
            if ($this->app->runningInConsole()) {
                $this->initCliTracer($event->command);
            }
            $name = 'Command ' . $event->command;

            $this->addCustomSegment(
                (new JobSegment())->setName($name)->setPayload(['arguments' => $event->input->getArguments(), 'options' => $event->input->getOptions()]),
                $name,
            );
        });

        $this->app['events']->listen(CommandFinished::class, function (CommandFinished $event) {
            $name = 'Command ' . $event->command;
            if ($this->hasAddedSegment($name)) {
                $this->getSegment($name)->setResult($event->exitCode === 0);
                $this->endSegment($name);
                $this->submitCliTracer();
            }
        });
    }
}
