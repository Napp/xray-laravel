<?php

declare(strict_types=1);

namespace Napp\Xray\Tests\Stubs;

use Illuminate\Contracts\Auth\Authenticatable;

class User implements Authenticatable
{
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): int
    {
        return 1;
    }

    public function getAuthPassword(): string
    {
        return 'foo';
    }

    public function getRememberToken(): string
    {
        return 'bar';
    }

    public function setRememberToken($value): void
    {
    }

    public function getRememberTokenName(): string
    {
        return 'baz';
    }
}
