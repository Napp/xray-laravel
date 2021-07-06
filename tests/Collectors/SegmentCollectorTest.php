<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Illuminate\Http\Request;
use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Segments\HttpSegment;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SegmentCollectorTest extends TestCase
{
    public function test_disable_trace_if_filtered()
    {
        $request = $this->createRequest();
        $request->expects($this->any())->method('path')->willReturn('filter/path');

        $segment = new SegmentCollector();
        $segment->initHttpTracer($request);

        $this->assertFalse($segment->tracer()->isSampled());
    }

    public function test_should_enable_trace()
    {
        $request = $this->createRequest();
        $request->expects($this->any())->method('path')->willReturn('normal/path');

        $segment = new SegmentCollector();
        $segment->initHttpTracer($request);

        $this->assertTrue($segment->tracer()->isSampled());
    }

    public function test_should_add_http_segment()
    {
        $collector = new SegmentCollector();
        $collector->addHttpSegment('example', 'http://example.com', 'POST');

        $data = $collector->getSegment('example')->jsonSerialize();
        $this->assertEquals('POST', $data['http']['request']['method']);
        $this->assertEquals('http://example.com', $data['http']['request']['url']);

        $collector->endHttpSegment('example', 400);

        $this->assertNull($collector->getSegment('example'));

        $data = $collector->current()->jsonSerialize()['subsegments'][0]->jsonSerialize();
        $this->assertEquals(400, $data['http']['response']['status']);
    }

    protected function createRequest(): MockObject
    {
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['ip', 'url', 'method', 'path'])
            ->getMock();

        $request->expects($this->any())->method('ip')->willReturn('some-ip');
        $request->expects($this->any())->method('url')->willReturn('some-url');
        $request->expects($this->any())->method('method')->willReturn('GET');

        return $request;
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('xray.enabled', true);
        $app['config']->set('xray.sample_rate', 100);
        $app['config']->set('xray.route_filters', ['filter/path']);
    }
}
