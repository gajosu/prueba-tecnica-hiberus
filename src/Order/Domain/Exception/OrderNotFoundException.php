<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class OrderNotFoundException extends DomainException
{
    public function __construct(string $orderId)
    {
        parent::__construct(sprintf('Order with id <%s> not found', $orderId));
    }
}

