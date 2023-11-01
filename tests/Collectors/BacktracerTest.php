<?php

namespace Napp\Xray\Tests\Collectors;

use Napp\Xray\Collectors\Backtracer;
use PHPUnit\Framework\TestCase;
class BacktracerTest extends TestCase
{
    public function test_it_should_not_crash_when_backtrace_is_empty() {
        // given a segment that implements the Backtracer trait
        $implementationClass = new class {
            use Backtracer;

            // and an override of the `getBacktrace` function to return an empty array
            public function getBacktrace(): array
            {
                return [];
            }

            // and a function that calls the `getCallerClass` function
            public function getResult() {
                $backtrace = $this->getBacktrace();

                return $this->getCallerClass($backtrace);
            }
        };

        // when calling the getResult class on the implementation
        $result = $implementationClass->getResult();

        // then it should return null
        $this->assertNull($result);
    }
}