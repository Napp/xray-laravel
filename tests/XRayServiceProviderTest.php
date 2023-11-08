<?php

namespace Napp\Xray\Tests;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Napp\Xray\Collectors\DatabaseQueryCollector;
use Napp\Xray\Collectors\FrameworkCollector;
use Napp\Xray\Collectors\JobCollector;
use Napp\Xray\Collectors\RouteCollector;
use Napp\Xray\Collectors\ViewCollector;
use Napp\Xray\Xray;
use Napp\Xray\XrayServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class XRayServiceProviderTest extends MockeryTestCase
{
    public function test_it_should_check_with_the_xray_singleton_when_registering_the_collectors() {
        // given a mock of the xray singleton
        $xraySingletonMock = \Mockery::mock(Xray::class, function(MockInterface $mock) {
            $mock->shouldReceive('shouldCaptureRequest')->andReturn(false);
            return $mock;
        });

        // and a mock config that return false for everything but the xray.enable config
        $configMock = \Mockery::mock(Repository::class, function(MockInterface $mock) {
            $mock->shouldReceive('get')->with('xray.enabled', \Mockery::andAnyOtherArgs())->andReturn(true);
            $mock->shouldReceive('get')->andReturn(false);

            return $mock;
        });

        // and a mock of the application
        $applicationMock = $this->getMockedApplication();

        $applicationMock->bind('xray', function() use ($xraySingletonMock) {
            return $xraySingletonMock;
        });

        $applicationMock->bind('config', function() use ($configMock) {
            return $configMock;
        });

        // and a XrayServiceProvider
        $provider = new XrayServiceProvider($applicationMock);

        // when booting the provider
        $provider->boot();

        // then it should have check if the request should be capture, on the xray singleton object
        $xraySingletonMock->shouldHaveReceived('shouldCaptureRequest');
    }

    public function test_it_should_not_register_collector_if_request_should_not_be_captured() {
        // given a mock of the xray singleton
        $xraySingletonMock = \Mockery::mock(Xray::class, function(MockInterface $mock) {
            $mock->shouldReceive('shouldCaptureRequest')->andReturn(false);
            return $mock;
        });

        // and a mock config that return true for everything
        $configMock = \Mockery::mock(Repository::class, function(MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn(true);

            return $mock;
        });

        // and a mock of the application
        $applicationMock = $this->getMockedApplication();

        $applicationMock->bind('xray', function() use ($xraySingletonMock) {
            return $xraySingletonMock;
        });

        $applicationMock->bind('config', function() use ($configMock) {
            return $configMock;
        });


        // and a XrayServiceProvider
        $provider = new XrayServiceProvider($applicationMock);

        // when booting the provider
        $provider->boot();

        // then it should not have registered the collectors
        $applicationMock->shouldNotHaveReceived('make', [DatabaseQueryCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldNotHaveReceived('make', [JobCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldNotHaveReceived('make', [ViewCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldNotHaveReceived('make', [RouteCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldNotHaveReceived('make', [FrameworkCollector::class, \Mockery::andAnyOtherArgs()]);
    }

    public function test_it_should_register_collectors_if_request_has_to_be_registered() {
        // given a mock of the xray singleton
        $xraySingletonMock = \Mockery::mock(Xray::class, function(MockInterface $mock) {
            $mock->shouldReceive('shouldCaptureRequest')->andReturn(true);
            return $mock;
        });

        // and a mock config that return true for everything
        $configMock = \Mockery::mock(Repository::class, function(MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn(true);

            return $mock;
        });

        // and a mock of the application
        $applicationMock = $this->getMockedApplication();

        $applicationMock->bind('xray', function() use ($xraySingletonMock) {
            return $xraySingletonMock;
        });

        $applicationMock->bind('config', function() use ($configMock) {
            return $configMock;
        });

        $applicationMock->shouldReceive('make')->with(DatabaseQueryCollector::class, \Mockery::andAnyOtherArgs())->andReturn();
        $applicationMock->shouldReceive('make')->with(JobCollector::class, \Mockery::andAnyOtherArgs())->andReturn();
        $applicationMock->shouldReceive('make')->with(ViewCollector::class, \Mockery::andAnyOtherArgs())->andReturn();
        $applicationMock->shouldReceive('make')->with(RouteCollector::class, \Mockery::andAnyOtherArgs())->andReturn();
        $applicationMock->shouldReceive('make')->with(FrameworkCollector::class, \Mockery::andAnyOtherArgs())->andReturn();


        // and a XrayServiceProvider
        $provider = new XrayServiceProvider($applicationMock);

        // when booting the provider
        $provider->boot();

        // then it should not have registered the collectors
        $applicationMock->shouldHaveReceived('make', [DatabaseQueryCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldHaveReceived('make', [JobCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldHaveReceived('make', [ViewCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldHaveReceived('make', [RouteCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldHaveReceived('make', [FrameworkCollector::class, \Mockery::andAnyOtherArgs()]);
    }

    public function test_it_should_register_collectors_if_running_in_console() {
        // given a mock of the xray singleton
        $xraySingletonMock = \Mockery::mock(Xray::class, function(MockInterface $mock) {
            $mock->shouldReceive('shouldCaptureRequest')->andReturn(false);
            return $mock;
        });

        // and a mock config that return true for everything
        $configMock = \Mockery::mock(Repository::class, function(MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn(true);

            return $mock;
        });

        // and a mock of the application that runs in console
        $applicationMock = $this->getMockedApplication(true);

        $applicationMock->bind('xray', function() use ($xraySingletonMock) {
            return $xraySingletonMock;
        });

        $applicationMock->bind('config', function() use ($configMock) {
            return $configMock;
        });

        $applicationMock->shouldReceive('make')->with(DatabaseQueryCollector::class, \Mockery::andAnyOtherArgs())->andReturn();
        $applicationMock->shouldReceive('make')->with(JobCollector::class, \Mockery::andAnyOtherArgs())->andReturn();
        $applicationMock->shouldReceive('make')->with(ViewCollector::class, \Mockery::andAnyOtherArgs())->andReturn();
        $applicationMock->shouldReceive('make')->with(RouteCollector::class, \Mockery::andAnyOtherArgs())->andReturn();
        $applicationMock->shouldReceive('make')->with(FrameworkCollector::class, \Mockery::andAnyOtherArgs())->andReturn();


        // and a XrayServiceProvider
        $provider = new XrayServiceProvider($applicationMock);

        // when booting the provider
        $provider->boot();

        // then it should not have registered the collectors
        $applicationMock->shouldHaveReceived('make', [DatabaseQueryCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldHaveReceived('make', [JobCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldHaveReceived('make', [ViewCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldHaveReceived('make', [RouteCollector::class, \Mockery::andAnyOtherArgs()]);
        $applicationMock->shouldHaveReceived('make', [FrameworkCollector::class, \Mockery::andAnyOtherArgs()]);
    }

    private function getMockedApplication($runningInConsole = false) {
        $configMock = \Mockery::mock(Repository::class, function(MockInterface $mock) {
            $mock->shouldReceive('get')->with('xray.use_lambda_invocation_context', \Mockery::andAnyOtherArgs())->andReturn(false);
            $mock->shouldReceive('get')->andReturn(true);

            return $mock;
        });

        $eventsMock = \Mockery::mock(Dispatcher::class, function(MockInterface $mock) {
            $mock->shouldReceive('listen');

            return $mock;
        });

        $requestMock = new Request();

        $applicationMock = \Mockery::mock(Application::class, function(MockInterface $mock) use ($runningInConsole) {
            $mock->shouldReceive('runningInConsole')->andReturn($runningInConsole);
            $mock->shouldReceive('make')->with('Illuminate\Foundation\Application', \Mockery::andAnyOtherArgs())->andReturnSelf();

            return $mock;
        })->makePartial();

        $applicationMock->bind('config', function() use ($configMock) {
            return $configMock;
        });

        $applicationMock->bind('request', function() use ($requestMock) {
            return $requestMock;
        });
        $applicationMock->bind('events', function() use ($eventsMock) {
            return $eventsMock;
        });

        Container::setInstance($applicationMock);

        return $applicationMock;
    }
}