<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

interface UuidGenerator
{
    public function generate(): string;
}

