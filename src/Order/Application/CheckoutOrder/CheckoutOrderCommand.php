<?php

declare(strict_types=1);

namespace App\Order\Application\CheckoutOrder;

final class CheckoutOrderCommand
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerId,
        public readonly string $paymentMethod = 'simulated'
    ) {
    }
}

