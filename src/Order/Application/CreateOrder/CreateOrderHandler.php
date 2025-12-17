<?php

declare(strict_types=1);

namespace App\Order\Application\CreateOrder;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Entity\OrderItem;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Order\Domain\Repository\OrderRepository;
use App\Product\Domain\Exception\ProductNotFoundException;
use App\Product\Domain\Repository\ProductRepository;
use App\Shared\Domain\Service\UuidGenerator;

final class CreateOrderHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ProductRepository $productRepository,
        private readonly UuidGenerator $uuidGenerator
    ) {
    }

    public function __invoke(CreateOrderCommand $command): string
    {
        $orderId = $this->uuidGenerator->generate();
        $order = new Order($orderId, $command->customerId);

        foreach ($command->items as $item) {
            $product = $this->productRepository->findById($item['productId']);

            if (!$product) {
                throw new ProductNotFoundException($item['productId']);
            }

            if (!$product->hasStock($item['quantity'])) {
                throw new InsufficientStockException($product->name());
            }

            $orderItem = new OrderItem(
                $this->uuidGenerator->generate(),
                $order,
                $product->id(),
                $product->name(),
                $product->price(),
                $item['quantity']
            );

            $order->addItem($orderItem);

            // Decrease product stock
            $product->decreaseStock($item['quantity']);
            $this->productRepository->save($product);
        }

        $this->orderRepository->save($order);
        $this->orderRepository->flush();

        return $orderId;
    }
}

