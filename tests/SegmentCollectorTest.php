<?php

declare(strict_types=1);

namespace Napp\Xray\Tests;

use Illuminate\Support\Facades\Auth;
use Napp\Xray\Tests\Stubs\CustomUserResolver;
use Napp\Xray\Tests\Stubs\User;
use Napp\Xray\Collectors\SegmentCollector;
use Napp\Xray\XrayServiceProvider;
use Orchestra\Testbench\TestCase;

class SegmentCollectorTest extends TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            XrayServiceProvider::class,
        ];
    }

    public function test_get_user_when_logged_in()
    {
        Auth::login(new User());

        $collector = new SegmentCollector();

        $this->assertSame('1', $collector->getUser());
    }

    public function test_get_user_when_guest()
    {
        $collector = new SegmentCollector();

        $this->assertNull($collector->getUser());
    }

    public function test_get_user_when_config_is_missing()
    {
        $config = config('xray');
        unset($config['user-resolver']);
        config()->set('xray', $config);

        Auth::login(new User());

        $collector = new SegmentCollector();

        $this->assertSame('1', $collector->getUser());
    }

    public function test_get_user_when_custom_resolver_is_configured()
    {
        config()->set('xray.user-resolver', CustomUserResolver::class);

        $collector = new SegmentCollector();

        $this->assertSame('foo', $collector->getUser());
    }
}
