<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Illuminate\Http\Request;
use Napp\Xray\Collectors\SegmentCollector;
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
        $segment = $collector->addHttpSegment('http://example.com', ['method' => 'POST', 'name' => 'example']);

        $data = $collector->getSegment('example')->jsonSerialize();
        $this->assertEquals('POST', $data['http']['request']['method']);
        $this->assertEquals('http://example.com', $data['http']['request']['url']);

        $segment->setResponseCode(400);

        $collector->endSegment('example');

        $this->assertNull($collector->getSegment('example'));

        $data = $collector->current()->jsonSerialize()['subsegments'][0]->jsonSerialize();
        $this->assertEquals(400, $data['http']['response']['status']);
    }

    public function test_end_empty_segments_wont_throw_exception()
    {
        $collector = new SegmentCollector();
        $collector->endSegment('some-segment');
        $collector->hasAddedSegment('some-segment');
        $collector->getSegment('some-segment');

        $this->assertTrue(true);
    }

    protected function createRequest(): MockObject
    {
        $request = $this->getMockBuilder(Request::class)->getMock();

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
