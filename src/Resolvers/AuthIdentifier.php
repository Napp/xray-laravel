<?php

declare(strict_types=1);

namespace Napp\Xray\Resolvers;

use Illuminate\Support\Facades\Auth;
use Napp\Xray\Resolvers\Contracts\ResolvesUser;

class AuthIdentifier implements ResolvesUser
{
    public function getUser(): ?string
    {
        if (app()->bound('auth') && Auth::check()) {
            return (string) Auth::user()->getAuthIdentifier();
        }

        return null;
    }
}
