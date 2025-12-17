<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class InsufficientStockException extends DomainException
{
    public function __construct(string $productName)
    {
        parent::__construct(sprintf('Insufficient stock for product <%s>', $productName));
    }
}

