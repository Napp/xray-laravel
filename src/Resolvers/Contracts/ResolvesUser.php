<?php

declare(strict_types=1);

namespace Napp\Xray\Resolvers\Contracts;

interface ResolvesUser
{
    public function getUser(): ?string;
}
