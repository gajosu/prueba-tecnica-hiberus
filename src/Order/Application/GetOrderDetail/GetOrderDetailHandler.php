<?php

declare(strict_types=1);

namespace App\Order\Application\GetOrderDetail;

use App\Order\Domain\Exception\OrderNotFoundException;
use App\Order\Domain\Repository\OrderRepository;

final class GetOrderDetailHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository
    ) {
    }

    public function __invoke(GetOrderDetailQuery $query): array
    {
        $order = $this->orderRepository->findByIdAndCustomer(
            $query->orderId,
            $query->customerId
        );

        if (!$order) {
            throw new OrderNotFoundException($query->orderId);
        }

        return [
            'id' => $order->id(),
            'customer_id' => $order->customerId(),
            'status' => $order->status()->value,
            'total' => $order->total()->amount(),
            'currency' => $order->total()->currency(),
            'items' => array_map(fn($item) => [
                'id' => $item->id(),
                'product_id' => $item->productId(),
                'product_name' => $item->productName(),
                'unit_price' => $item->unitPrice()->amount(),
                'quantity' => $item->quantity(),
                'subtotal' => $item->subtotal()->amount(),
            ], $order->items()->toArray()),
            'created_at' => $order->createdAt()->format('Y-m-d H:i:s'),
            'paid_at' => $order->paidAt()?->format('Y-m-d H:i:s'),
        ];
    }
}

