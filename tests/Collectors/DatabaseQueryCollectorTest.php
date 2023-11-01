<?php

namespace Napp\Xray\Tests\Collectors;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Application;
use Napp\Xray\Collectors\DatabaseQueryCollector;
use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Segment;

class DatabaseQueryCollectorTest extends TestCase
{
    public function test_it_should_handle_null_callerClass()
    {
        // given a mock implementation of the Application Object
        $mockApplication = new DatabaseQueryCollectorTestMockApplication();


        // given a DatabaseQueryCollector object
        $databaseQueryCollectorMock = new class($mockApplication) extends DatabaseQueryCollector {
            protected $currentSegment;
            // and an overridden getBacktrace function that returns an empty array
            public function getBacktrace(): array
            {
                return [];
            }

            protected function checkForEnabledBindings(): void {}

            // and a defined current segment
            public function current(): Segment
            {
                if(is_null($this->currentSegment)) {
                    $this->currentSegment = new Segment();
                    $this->currentSegment->begin();
                }

                return $this->currentSegment;
            }
        };

        // and an event for this DatabaseQueryCollectorMock
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->method('getName')
            ->willReturn('irrelevent');

        $connectionMock->method('getDriverName')
            ->willReturn('irrelevent');

        $givenEvent = new QueryExecuted("irrelevent", [], 1000, $connectionMock);

        // when dispatching the event on the mock application
        $mockApplication->events->dispatch($givenEvent);

        // then a sub segment should have been dispatched
        $currentSegment = $databaseQueryCollectorMock->current();
        $currentSegmentData = $currentSegment->jsonSerialize();

        $this->assertCount(1, $currentSegmentData['subsegments']);

        // and the sub segment name should contain "too deeply nested"
        /** @var Segment $firstSubSegment */
        $firstSubSegment = $currentSegmentData['subsegments'][0];
        $firstSubSegmentData = $firstSubSegment->jsonSerialize();

        $this->assertStringContainsString("too deeply nested", $firstSubSegmentData['name']);
        $this->assertStringNotContainsString(" at ", $firstSubSegmentData['name']);
    }
}

class DatabaseQueryCollectorTestMockApplication extends Application
{

    public $events;

    public function __construct()
    {
        $this->events = new DatabaseQueryCollectorTestMockEvents();
    }
}

class DatabaseQueryCollectorTestMockEvents
{
    protected $listeners = [];

    public function listen(string $event, $callback)
    {
        $this->listeners[$event] = $callback;
    }

    public function dispatch($eventObject)
    {
        $eventClass = get_class($eventObject);

        if (!array_key_exists($eventClass, $this->listeners)) {
            throw new \Exception("Unit test exception, $eventClass was never registered");
        }

        $eventCallback = $this->listeners[$eventClass];

        $eventCallback($eventObject);
    }
}