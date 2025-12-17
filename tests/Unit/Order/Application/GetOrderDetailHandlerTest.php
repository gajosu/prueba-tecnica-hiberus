<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order\Application;

use App\Order\Application\GetOrderDetail\GetOrderDetailHandler;
use App\Order\Application\GetOrderDetail\GetOrderDetailQuery;
use App\Order\Domain\Exception\OrderNotFoundException;
use App\Order\Domain\Repository\OrderRepository;
use App\Tests\Shared\Mother\OrderMother;
use App\Tests\Shared\UnitTestCase;

final class GetOrderDetailHandlerTest extends UnitTestCase
{
    private OrderRepository $orderRepository;
    private GetOrderDetailHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->orderRepository = $this->mockRepository(OrderRepository::class);
        $this->handler = new GetOrderDetailHandler($this->orderRepository);
    }

    public function test_it_returns_order_detail(): void
    {
        // Arrange
        $order = OrderMother::withItems(2);
        $query = new GetOrderDetailQuery(
            orderId: $order->id(),
            customerId: $order->customerId()
        );

        $this->orderRepository
            ->expects($this->once())
            ->method('findByIdAndCustomer')
            ->with($order->id(), $order->customerId())
            ->willReturn($order);

        // Act
        $result = ($this->handler)($query);

        // Assert
        $this->assertArrayHasKeys(['id', 'customer_id', 'status', 'total', 'items'], $result);
        $this->assertEquals($order->id(), $result['id']);
        $this->assertCount(2, $result['items']);
    }

    public function test_it_throws_exception_when_order_not_found(): void
    {
        // Arrange
        $query = new GetOrderDetailQuery(
            orderId: 'non-existent',
            customerId: 'customer-123'
        );

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn(null);

        // Assert
        $this->expectException(OrderNotFoundException::class);

        // Act
        ($this->handler)($query);
    }

    public function test_it_validates_customer_ownership(): void
    {
        // Arrange
        $query = new GetOrderDetailQuery(
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
        ($this->handler)($query);
    }

    public function test_it_returns_order_items_detail(): void
    {
        // Arrange
        $order = OrderMother::withItems(1);
        $query = new GetOrderDetailQuery(
            orderId: $order->id(),
            customerId: $order->customerId()
        );

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn($order);

        // Act
        $result = ($this->handler)($query);

        // Assert
        $item = $result['items'][0];
        $this->assertArrayHasKeys(['id', 'product_id', 'product_name', 'unit_price', 'quantity', 'subtotal'], $item);
    }
}

