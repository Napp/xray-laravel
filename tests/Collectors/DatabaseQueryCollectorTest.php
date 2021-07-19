<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Illuminate\Database\Connection;
use Napp\Xray\Collectors\DatabaseQueryCollector;
use Napp\Xray\Segments\Trace;
use Orchestra\Testbench\TestCase;

class DatabaseQueryCollectorTest extends TestCase
{
    public function test_return_query_if_count_not_match()
    {
        $connection = $this->createMock(Connection::class);

        $connection->expects($this->once())
            ->method('getName')
            ->willReturn('name');
        $connection->expects($this->once())
            ->method('getDriverName')
            ->willReturn('driver-name');
        $connection->expects($this->never())->method('prepareBindings');

        $collector = new DatabaseQueryCollector($this->app);
        $collector->current()->begin();
        $collector->handleQueryReport('abc ? def ? ?', [1, 2], 0, $connection);

        $serialized = $collector->current()->jsonSerialize();
        $querySerialized = $serialized['subsegments'][0]->jsonSerialize();

        $this->assertEquals('name', $querySerialized['name']);
        $this->assertEquals('driver-name', $querySerialized['sql']['database_type']);
        $this->assertEquals('abc ? def ? ?', $querySerialized['sql']['sanitized_query']);
    }

    public function test_binding_correctly()
    {
        $connection = $this->createMock(Connection::class);

        $connection->expects($this->once())
            ->method('getName')
            ->willReturn('name');
        $connection->expects($this->once())
            ->method('getDriverName')
            ->willReturn('driver-name');
        $connection->expects($this->once())
            ->method('prepareBindings')
            ->willReturn([123, 'ghi']);

        $collector = new DatabaseQueryCollector($this->app);
        $collector->current()->begin();
        $collector->handleQueryReport('abc ? def ?', [1, 2], 0, $connection);

        $serialized = $collector->current()->jsonSerialize();
        $querySerialized = $serialized['subsegments'][0]->jsonSerialize();

        $this->assertEquals("abc 123 def 'ghi'", $querySerialized['sql']['sanitized_query']);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('xray.db_bindings', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Trace::flush();
    }
}
