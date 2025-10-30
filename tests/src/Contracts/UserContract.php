<?php

declare(strict_types=1);

namespace Tests\Contracts;

interface UserContract
{

    public function username(): string;
    public function email(): string;
    public function active(): bool;
}
