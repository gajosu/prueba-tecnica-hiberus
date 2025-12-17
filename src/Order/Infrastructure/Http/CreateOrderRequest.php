<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Http;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrderRequest
{
    /**
     * @param OrderItemRequest[] $items
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Items are required')]
        #[Assert\Count(min: 1, minMessage: 'Order must have at least one item')]
        #[Assert\Valid]
        public readonly array $items = []
    ) {
    }
}

final class OrderItemRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Product ID is required')]
        public readonly string $productId,

        #[Assert\NotBlank(message: 'Quantity is required')]
        #[Assert\Positive(message: 'Quantity must be greater than 0')]
        #[Assert\Type(type: 'integer', message: 'Quantity must be an integer')]
        public readonly int $quantity
    ) {
    }
}

