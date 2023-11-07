<?php

namespace Napp\Xray\Tests;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Mockery\MockInterface;
use Monolog\Test\TestCase;
use Napp\Xray\Xray;
use Napp\Xray\XrayServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class XRayServiceProviderTest extends TestCase
{
    public function test_it_should_check_with_the_xray_singleton_when_registering_the_collectors() {
        // given a mock of the xray singleton
        $xraySingletonMock = \Mockery::mock(Xray::class, function(MockInterface $mock) {
            $mock->shouldReceive('shouldCaptureRequest')->andReturn(false);
            return $mock;
        });

        // and a mock of the application
        $configMock = \Mockery::mock(Repository::class, function(MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn(true);

            return $mock;
        });

        $requestMock = new Request();

        $applicationMock = \Mockery::mock(Application::class, function(MockInterface $mock) {
            $mock->shouldReceive('runningInConsole')->andReturn(false);

            return $mock;
        })->makePartial();

        $applicationMock->bind('xray', function() use ($xraySingletonMock) {
            return $xraySingletonMock;
        });

        $applicationMock->bind('config', function() use ($configMock) {
            return $configMock;
        });

        $applicationMock->bind('request', function() use ($requestMock) {
            return $requestMock;
        });

        Container::setInstance($applicationMock);

        // and an implementation of XrayServiceProvider that expose the `registerCollectors` function
        $provider = new class($applicationMock) extends XrayServiceProvider {
            public function callRegisterCollectors() {
                $this->registerCollectors();
            }
        };

        // when registering the collectors
        $provider->callRegisterCollectors();

        // then it should have check if the request should be capture, on the xray singleton object
        $xraySingletonMock->shouldHaveReceived('shouldCaptureRequest');
    }
}