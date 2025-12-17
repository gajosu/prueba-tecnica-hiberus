<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security\Attribute;

use Attribute;

/**
 * Attribute to mark a controller action as requiring authentication
 * Usage: #[RequiresAuth]
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class RequiresAuth
{
}

