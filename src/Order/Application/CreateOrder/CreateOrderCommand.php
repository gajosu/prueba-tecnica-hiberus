<?php

declare(strict_types=1);

namespace App\Order\Application\CreateOrder;

final class CreateOrderCommand
{
    /**
     * @param array<array{productId: string, quantity: int}> $items
     */
    public function __construct(
        public readonly string $customerId,
        public readonly array $items
    ) {
    }
}

