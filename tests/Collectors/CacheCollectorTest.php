<?php

namespace Napp\Xray\Tests\Collectors;

use Illuminate\Cache\Events\CacheHit;
use Monolog\Test\TestCase;
use \Illuminate\Foundation\Application;
use Napp\Xray\Collectors\CacheCollector;
use Pkerrigan\Xray\Segment;

class CacheCollectorTest extends TestCase
{
    public function test_it_should_handle_null_callerClass() {
        // given a mock implementation of the Application Object
        $mockApplication = new CacheCollectorTestMockApplication();

        // given a CacheCollector object
        $cacheCollectorMock = new class($mockApplication) extends CacheCollector {
            // and an overridden getBacktrace function that returns an empty array
            public function getBacktrace(): array
            {
                return [];
            }

            // and a custom public function that returns the segments
            public function getSegments(): array {
                return $this->segments;
            }
        };

        // and an event for this cacheCollectorMock
        $givenEvent = new CacheHit("irrelevent", "irrelevent", "irrelevent");

        // when dispatching the event on the mock application
        $mockApplication->events->dispatch($givenEvent);

        // then a segment should have been dispatched
        $this->assertCount(1, $cacheCollectorMock->getSegments());

        // and the segment name should contain "too deeply nested"
        /** @var Segment $firstSegment */
        $firstSegment = array_values($cacheCollectorMock->getSegments())[0];
        $firstSegmentData = $firstSegment->jsonSerialize();

        $this->assertStringContainsString("too deeply nested", $firstSegmentData['name']);
        $this->assertStringNotContainsString(" at ", $firstSegmentData['name']);
    }
}

class CacheCollectorTestMockApplication extends Application {

    public $events;
    public function __construct()
    {
        $this->events = new CacheCollectorTestMockEvents();
    }
}

class CacheCollectorTestMockEvents {
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