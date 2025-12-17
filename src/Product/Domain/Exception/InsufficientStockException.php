<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class InsufficientStockException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}

