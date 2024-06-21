<?php

namespace Napp\Xray\Tests\Collectors;

use Illuminate\Config\Repository;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Napp\Xray\Collectors\CommandCollector;
use Napp\Xray\Segments\Trace;
use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\Submission\SegmentSubmitter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CommandCollectorTest extends TestCase
{
    public function test_it_creates_segment() {
        // given a mock implementation of the Application Object
        $mockApplication = new CommandCollectorTestMockApplication();
        $mockApplication->bind('config', function() {
            return new Repository([
                'xray' => [
                    'enabled' => true,
                    'submitter' => CommandCollectorSegmentSubmitter::class,
                ],
            ]);
        });
        Container::setInstance($mockApplication);
        CommandCollectorSegmentSubmitter::$submittedSegments = [];

        // given a CommandCollector object
        $commandCollector = new CommandCollector($mockApplication);
        // with a tracer that forces sampling
        $commandCollector->tracer()->setSampled(true);

        // and the events our collector listens to
        $startedEvent = new CommandStarting("foobar", new ArrayInput([]), new NullOutput());
        $finishedEvent = new CommandFinished("foobar", new ArrayInput([]), new NullOutput(), 0);

        // after dispatching the events on the mock application
        $mockApplication->events->dispatch($startedEvent);
        $mockApplication->events->dispatch($finishedEvent);

        // then a trace should have been submitted
        $this->assertCount(1, CommandCollectorSegmentSubmitter::$submittedSegments);
        $trace = json_decode(json_encode(CommandCollectorSegmentSubmitter::$submittedSegments[0]), true);

        // with a segment named "Command foobar"
        $this->assertEquals("Command foobar", $trace["subsegments"][0]["name"]);
    }
}

class CommandCollectorTestMockApplication extends Application {

    public $events;
    public function __construct()
    {
        $this->events = new CommandCollectorTestMockEvents();
        $this->instance('events', $this->events);
    }
}

class CommandCollectorTestMockEvents {
    protected $listeners = [];

    public function listen(string $event, $callback) {
        $this->listeners[$event] = $callback;
    }

    public function dispatch($eventObject) {
        $eventClass = get_class($eventObject);

        if(!array_key_exists($eventClass, $this->listeners)) {
            throw new \Exception("Unit test exception, $eventClass was never registered");
        }

        $eventCallback = $this->listeners[$eventClass];

        $eventCallback($eventObject);
    }
}

class CommandCollectorSegmentSubmitter implements SegmentSubmitter
{

    public static $submittedSegments = [];

    public function submitSegment(Segment $segment): void
    {
        self::$submittedSegments[] = $segment;
    }

}
