<?php

declare(strict_types=1);

namespace App\Order\Application\CheckoutOrder;

use App\Order\Domain\Exception\OrderNotFoundException;
use App\Order\Domain\Repository\OrderRepository;
use App\Product\Domain\Exception\InsufficientStockException;
use App\Product\Domain\Repository\ProductRepository;

final class CheckoutOrderHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ProductRepository $productRepository
    ) {
    }

    public function __invoke(CheckoutOrderCommand $command): array
    {
        $order = $this->orderRepository->findByIdAndCustomer(
            $command->orderId,
            $command->customerId
        );

        if (!$order) {
            throw new OrderNotFoundException($command->orderId);
        }

        // Verify stock availability before processing payment
        foreach ($order->items() as $orderItem) {
            $product = $this->productRepository->findById($orderItem->productId());

            if (!$product) {
                throw new \DomainException("Product {$orderItem->productName()} no longer exists");
            }

            if (!$product->hasStock($orderItem->quantity())) {
                throw new InsufficientStockException(
                    "Insufficient stock for {$orderItem->productName()}. Available: {$product->stock()}, Required: {$orderItem->quantity()}"
                );
            }
        }

        // Simulate payment processing
        $paymentSuccess = $this->simulatePayment($command->paymentMethod);

        if (!$paymentSuccess) {
            throw new \DomainException('Payment failed');
        }

        // Reduce stock after successful payment
        foreach ($order->items() as $orderItem) {
            $product = $this->productRepository->findById($orderItem->productId());
            $product->reduceStock($orderItem->quantity());
        }

        $order->markAsPaid();
        $this->orderRepository->flush();

        return [
            'order_id' => $order->id(),
            'status' => $order->status()->value,
            'total' => $order->total()->amount(),
            'currency' => $order->total()->currency(),
            'paid_at' => $order->paidAt()?->format('Y-m-d H:i:s'),
            'message' => 'Payment processed successfully',
        ];
    }

    private function simulatePayment(string $paymentMethod): bool
    {
        // Simulated payment always succeeds
        // In a real application, this would integrate with a payment gateway
        return true;
    }
}

