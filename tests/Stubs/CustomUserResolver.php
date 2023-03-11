<?php

declare(strict_types=1);

namespace Napp\Xray\Tests\Stubs;

use Napp\Xray\Resolvers\Contracts\ResolvesUser;

class CustomUserResolver implements ResolvesUser
{
    public function getUser(): ?string
    {
        return 'foo';
    }
}
