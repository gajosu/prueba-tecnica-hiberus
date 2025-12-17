<?php

declare(strict_types=1);

namespace App\Order\Application\CheckoutOrder;

use App\Order\Domain\Exception\OrderNotFoundException;
use App\Order\Domain\Repository\OrderRepository;

final class CheckoutOrderHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository
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

        // Simulate payment processing
        $paymentSuccess = $this->simulatePayment($command->paymentMethod);

        if (!$paymentSuccess) {
            throw new \DomainException('Payment failed');
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

