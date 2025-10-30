<?php

declare(strict_types=1);

namespace Tests\DTOs;

final class UserDto
{

    public function __construct(
        private readonly string $username,
        private readonly string $email,
        private readonly bool $active
    ) {}

    public function username(): string
    {
        return $this->username;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function active(): bool
    {

        return $this->active;
    }
}
