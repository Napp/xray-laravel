<?php

declare(strict_types=1);

namespace Napp\Xray\Facades;

use Illuminate\Support\Facades\Facade;

class Xray extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return 'xray';
    }
}
