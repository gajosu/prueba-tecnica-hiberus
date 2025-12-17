<?php

declare(strict_types=1);

namespace App\Order\Application\GetOrderDetail;

final class GetOrderDetailQuery
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerId
    ) {
    }
}

