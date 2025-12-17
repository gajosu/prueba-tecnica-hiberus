<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\UuidGenerator;

/**
 * Fake UUID Generator for testing purposes
 * Generates predictable UUIDs for easier test assertions
 */
final class FakeUuidGenerator implements UuidGenerator
{
    private int $counter = 1;
    private ?string $fixedUuid = null;

    public function generate(): string
    {
        if ($this->fixedUuid !== null) {
            return $this->fixedUuid;
        }

        return sprintf(
            '00000000-0000-0000-0000-%012d',
            $this->counter++
        );
    }

    /**
     * Set a fixed UUID to be returned on all generate() calls
     */
    public function setFixedUuid(string $uuid): void
    {
        $this->fixedUuid = $uuid;
    }

    /**
     * Reset to sequential UUID generation
     */
    public function reset(): void
    {
        $this->counter = 1;
        $this->fixedUuid = null;
    }
}

