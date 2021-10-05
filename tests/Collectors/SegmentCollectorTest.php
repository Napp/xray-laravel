<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Illuminate\Http\Request;
use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Config\HttpSegmentConfig;
use Napp\Xray\Config\SegmentConfig;
use Napp\Xray\Segments\Trace;
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
        $collector->current()->begin();
        $segment = $collector->addHttpSegment(new HttpSegmentConfig([
            HttpSegmentConfig::NAME => 'example',
            HttpSegmentConfig::METHOD => 'POST',
            HttpSegmentConfig::URL => 'http://example.com',
        ]));

        $this->assertTrue($collector->nameExist('example'));
        $this->assertNotEmpty($collector->getSegmentByName('example'));

        $data = $collector->getSegmentById($segment->getId())->jsonSerialize();
        $this->assertEquals('POST', $data['http']['request']['method']);
        $this->assertEquals('http://example.com', $data['http']['request']['url']);

        $segment->setResponseCode(400);

        $collector->endSegmentByName('example');

        $this->assertEmpty($collector->getSegmentByName('example'));

        $data = $collector->current()->jsonSerialize()['subsegments'][0]->jsonSerialize();
        $this->assertEquals(400, $data['http']['response']['status']);
    }

    public function test_should_apply_config_correctly()
    {
        $collector = new SegmentCollector();
        $collector->current()->begin();
        $segment = $collector->addSegment(new SegmentConfig([
            SegmentConfig::NAME        => 'example',
            SegmentConfig::START_TIME  => 123,
            SegmentConfig::ANNOTATIONS => [
                'ann1' => 'ann1_value'
            ],
            SegmentConfig::METADATA    => [
                'meta1' => 'meta1_value'
            ],
        ]));

        $data = $collector->getSegmentById($segment->getId())->jsonSerialize();
        $this->assertEquals('example', $data['name']);
        $this->assertEquals('ann1_value', $data['annotations']['ann1']);
        $this->assertEquals('meta1_value', $data['metadata']['meta1']);
        $this->assertEquals(123, $data['start_time']);

        $collector->endSegmentById($segment->getId());

        $this->assertNull($collector->getSegmentById($segment->getId()));

        $data = $collector->current()->jsonSerialize()['subsegments'][0]->jsonSerialize();
        $this->assertNotNull($data['end_time']);
    }

    public function test_bind_same_parent_segment()
    {
        $collector = new SegmentCollector();
        $trace = $collector->current()->begin();

        $collector->addSegment(new SegmentConfig([
            SegmentConfig::NAME           => 'segment1',
            SegmentConfig::PARENT_SEGMENT => $trace,
        ]));
        $collector->addSegment(new SegmentConfig([
            SegmentConfig::NAME           => 'segment2',
            SegmentConfig::PARENT_SEGMENT => $trace,
        ]));

        $collector->endSegmentByName('segment1');
        $collector->endSegmentByName('segment2');

        $trace->end();

        $this->assertEquals(count($trace->jsonSerialize()['subsegments']), 2);
    }

    public function test_bind_hierarchy()
    {
        $collector = new SegmentCollector();
        $trace = $collector->current()->begin();

        $collector->addSegment(new SegmentConfig([
            SegmentConfig::NAME           => 'segment1',
        ]));
        $collector->addSegment(new SegmentConfig([
            SegmentConfig::NAME           => 'segment2',
        ]));

        $collector->endSegmentByName('segment1');
        $collector->endSegmentByName('segment2');

        $trace->end();

        $level1Segments = $trace->jsonSerialize()['subsegments'];
        $this->assertEquals(count($level1Segments), 1);
        $level2Segments = $level1Segments[0]->jsonSerialize()['subsegments'];
        $this->assertEquals(count($level2Segments), 1);
    }

    public function test_handle_exception()
    {
        // should not rethrow exception
        $this->app['config']->set('xray.ignore_error', true);
        $exception =  new \Exception('message', 0);
        $collector = new SegmentCollector();

        $collector->handleException($exception);

        $serialized = $collector->current()->jsonSerialize();
        $this->assertEquals('message', $serialized['annotations']['xrayError']);
        $this->assertNotEmpty(($serialized['metadata']['xrayDatabaseQueryTrace']));

        // should rethrow exception
        $this->app['config']->set('xray.ignore_error', false);
        $this->expectException(\Exception::class);

        $collector->handleException($exception);
    }

    protected function createRequest()
    {
        $request = $this->createMock(Request::class);

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

    protected function setUp(): void
    {
        parent::setUp();

        Trace::flush();
    }
}
