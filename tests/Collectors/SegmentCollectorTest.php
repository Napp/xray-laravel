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

    public function test_add_http_segment()
    {
        $collector = $this->setupCollector();
        $segment = $collector
            ->addHttpSegment(new HttpSegmentConfig('example', 'http://example.com', 'post'))
            ->setResponseCode(400)
            ->end();

        $data = $segment->jsonSerialize();
        $this->assertEquals('example', $data['name']);
        $this->assertEquals('post', $data['http']['request']['method']);
        $this->assertEquals('http://example.com', $data['http']['request']['url']);
        $this->assertEquals(400, $data['http']['response']['status']);
    }

    public function test_add_segment()
    {
        $collector = $this->setupCollector();
        $segment = $collector->addSegment(
            (new SegmentConfig('example'))
                ->setAnnotations(['ann1' => 'ann1_value'])
                ->setMetadata(['meta1' => 'meta1_value'])
        )->end();

        $data = $segment->jsonSerialize();
        $this->assertEquals('example', $data['name']);
        $this->assertEquals('ann1_value', $data['annotations']['ann1']);
        $this->assertEquals('meta1_value', $data['metadata']['meta1']);
        $this->assertNotNull($data['end_time']);
    }

    public function test_bind_same_parent_segment()
    {
        $collector = $this->setupCollector();
        $parent = $collector->getCurrentSegment();

        $segment1 = $collector->addSegment(
            (new SegmentConfig('segment1'))->setParent($parent)
        );
        $segment2 = $collector->addSegment(
            (new SegmentConfig('segment2'))->setParent($parent)
        );

        $segment1->end();
        $segment2->end();

        $subsegments = $parent->jsonSerialize()['subsegments'];
        $this->assertEquals(count($subsegments), 2);
    }

    public function test_bind_hierarchy()
    {
        $collector = $this->setupCollector();
        $parent = $collector->getCurrentSegment();

        $segment1 = $collector->addSegment();
        $segment2 = $collector->addSegment();

        $segment1->end();
        $segment2->end();
        $collector->endCurrentSegment();

        $level1Segments = $parent->jsonSerialize()['subsegments'];
        $this->assertEquals(count($level1Segments), 1);
        $level2Segments = $level1Segments[0]->jsonSerialize()['subsegments'];
        $this->assertEquals(count($level2Segments), 1);
    }

    public function test_handle_exception()
    {
        // should not rethrow exception
        $this->app['config']->set('xray.ignore_error', true);
        $exception =  new \Exception('message', 0);
        $collector = $this->setupCollector();

        $collector->handleException($exception);

        $serialized = $collector->getCurrentSegment()->jsonSerialize();
        $this->assertEquals('message', $serialized['annotations']['xrayError']);
        $this->assertNotEmpty(($serialized['metadata']['xrayDatabaseQueryTrace']));

        // should rethrow exception
        $this->app['config']->set('xray.ignore_error', false);
        $this->expectException(\Exception::class);

        $collector->handleException($exception);
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

    private function createRequest()
    {
        $request = $this->createMock(Request::class);

        $request->expects($this->any())->method('ip')->willReturn('some-ip');
        $request->expects($this->any())->method('url')->willReturn('some-url');
        $request->expects($this->any())->method('method')->willReturn('GET');

        return $request;
    }

    private function setupCollector(): SegmentCollector {
        $collector = new SegmentCollector();
        $collector->getCurrentSegment()->begin();

        return $collector;
    }
}
