<?php

declare(strict_types=1);

namespace Napp\Xray\Segments;

use Pkerrigan\Xray\Trace as BaseTrace;

class Trace extends BaseTrace
{
    /**
     * @var static
     */
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public static function flush()
    {
        self::$instance = new static();
    }
}
