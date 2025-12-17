<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security\Attribute;

use Attribute;

/**
 * Attribute to mark a controller action as requiring a specific role
 * Usage: #[RequiresRole('ADMIN')]
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class RequiresRole
{
    public function __construct(
        public readonly string $role
    ) {
    }
}

