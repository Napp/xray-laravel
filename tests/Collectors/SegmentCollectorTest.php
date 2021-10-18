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
use Pkerrigan\Xray\Segment;

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

    public function test_add_segment()
    {
        $collector = $this->setupCollector();
        $segmentId = $collector
            ->addSegment(
                (new Segment())
                    ->setName('example')
                    ->addAnnotation('ann1', 'ann1_value')
                    ->addMetadata('meta1', 'meta1_value')
            )
            ->end()
            ->getId();

        $data = $collector->getSegment($segmentId)->jsonSerialize();
        $this->assertEquals('example', $data['name']);
        $this->assertEquals('ann1_value', $data['annotations']['ann1']);
        $this->assertEquals('meta1_value', $data['metadata']['meta1']);
        $this->assertNotNull($data['end_time']);
    }

    public function test_bind_same_parent_segment()
    {
        $collector = $this->setupCollector();
        $parent = $collector->getCurrentSegment();

        $collector->addSegment(new Segment(), $parent->getId());
        $collector->addSegment(new Segment(), $parent->getId());

        $subsegments = $parent->jsonSerialize()['subsegments'];
        $this->assertEquals(2, count($subsegments));
    }

    public function test_bind_hierarchy()
    {
        $collector = $this->setupCollector();
        $parent = $collector->getCurrentSegment();

        $collector->addSegment(new Segment());
        $collector->addSegment(new Segment());

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
