<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order\Application;

use App\Order\Application\CheckoutOrder\CheckoutOrderCommand;
use App\Order\Application\CheckoutOrder\CheckoutOrderHandler;
use App\Order\Domain\Exception\OrderNotFoundException;
use App\Order\Domain\Repository\OrderRepository;
use App\Tests\Shared\Mother\OrderMother;
use App\Tests\Shared\UnitTestCase;

final class CheckoutOrderHandlerTest extends UnitTestCase
{
    private OrderRepository $orderRepository;
    private CheckoutOrderHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->orderRepository = $this->mockRepository(OrderRepository::class);
        $this->handler = new CheckoutOrderHandler($this->orderRepository);
    }

    public function test_it_marks_order_as_paid(): void
    {
        // Arrange
        $order = OrderMother::random();
        $command = new CheckoutOrderCommand(
            orderId: $order->id(),
            customerId: $order->customerId(),
            paymentMethod: 'simulated'
        );

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn($order);

        $this->orderRepository
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertTrue($order->status()->isPaid());
        $this->assertNotNull($order->paidAt());
        $this->assertEquals('paid', $result['status']);
    }

    public function test_it_throws_exception_for_invalid_order(): void
    {
        // Arrange
        $command = new CheckoutOrderCommand(
            orderId: 'non-existent',
            customerId: 'customer-123'
        );

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn(null);

        // Assert
        $this->expectException(OrderNotFoundException::class);

        // Act
        ($this->handler)($command);
    }

    public function test_it_simulates_payment_successfully(): void
    {
        // Arrange
        $order = OrderMother::random();
        $command = new CheckoutOrderCommand(
            orderId: $order->id(),
            customerId: $order->customerId(),
            paymentMethod: 'credit_card'
        );

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn($order);

        $this->orderRepository->method('flush');

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertArrayHasKeys(['order_id', 'status', 'total', 'paid_at', 'message'], $result);
        $this->assertEquals('Payment processed successfully', $result['message']);
    }

    public function test_it_validates_customer_ownership_before_checkout(): void
    {
        // Arrange
        $command = new CheckoutOrderCommand(
            orderId: 'order-123',
            customerId: 'wrong-customer'
        );

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->with('order-123', 'wrong-customer')
            ->willReturn(null);

        // Assert
        $this->expectException(OrderNotFoundException::class);

        // Act
        ($this->handler)($command);
    }
}

