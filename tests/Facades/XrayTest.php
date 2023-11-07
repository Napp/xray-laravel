<?php

namespace Napp\Xray\Tests\Facades;

use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\Xray;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\TestCase;

class XrayTest extends TestCase
{
    public function test_it_should_register_request_filtering_callback() {
        // given an Xray object
        /** @var Xray $xrayObject */
        $xrayObject = new Xray(new SegmentCollector());

        // and a mock of a callback to check if the current request should be captured.
        $callbackCalled = false;
        $requestCapturingCallbackMock = function(Request $request) use (&$callbackCalled) {
            $callbackCalled = true;
        };

        // and the callback has been registered on the facade as a filter callback
        $xrayObject->addRequestFilterCallback($requestCapturingCallbackMock);

        // when checking, on the facade, if the request should be captured
        $request = new Request();
        $xrayObject->shouldCaptureRequest($request);

        // then the callback should have been called
        $this->assertTrue($callbackCalled);
    }

    public function test_it_should_return_true_when_no_request_filter_callable_returns_false() {
        // given a xray object
        /** @var Xray $xrayObject */
        $xrayObject = new Xray(new SegmentCollector());

        // and some request filter callback that return true
        $givenRequestCallbackCount = 3;
        for($i = 0; $i < $givenRequestCallbackCount; $i ++) {
            $xrayObject->addRequestFilterCallback(function() {
                return true;
            });
        }

        // when checking, on the facade, if the request should be captured
        $request = new Request();
        $result = $xrayObject->shouldCaptureRequest($request);

        // then it should return true
        $this->assertTrue($result);
    }

    public function test_it_should_return_false_if_at_least_one_request_filter_callback_returns_false() {
        // given a xray object
        /** @var Xray $xrayObject */
        $xrayObject = new Xray(new SegmentCollector());

        // and some request filter callback that return true
        $givenRequestCallbackCount = 3;
        for($i = 0; $i < $givenRequestCallbackCount; $i ++) {
            $xrayObject->addRequestFilterCallback(function() {
                return true;
            });
        }

        // and one request filter callback that returns false
        $xrayObject->addRequestFilterCallback(function() {
            return false;
        });

        // when checking, on the facade, if the request should be captured
        $request = new Request();
        $result = $xrayObject->shouldCaptureRequest($request);

        // then it should return false
        $this->assertFalse($result);
    }

    public function test_it_should_return_true_if_no_request_filtering_callback_are_defined() {
        // given a xray object
        /** @var Xray $xrayObject */
        $xrayObject = new Xray(new SegmentCollector());

        // and no request filtering callback defined

        // when checking, on the facade, if the request should be captured
        $request = new Request();
        $result = $xrayObject->shouldCaptureRequest($request);

        // then it should return true
        $this->assertTrue($result);
    }
}