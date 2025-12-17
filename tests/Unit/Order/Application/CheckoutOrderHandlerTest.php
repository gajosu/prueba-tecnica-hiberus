<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order\Application;

use App\Order\Application\CheckoutOrder\CheckoutOrderCommand;
use App\Order\Application\CheckoutOrder\CheckoutOrderHandler;
use App\Order\Domain\Exception\OrderNotFoundException;
use App\Order\Domain\Repository\OrderRepository;
use App\Product\Domain\Exception\InsufficientStockException;
use App\Product\Domain\Repository\ProductRepository;
use App\Tests\Shared\Mother\OrderMother;
use App\Tests\Shared\Mother\ProductMother;
use App\Tests\Shared\UnitTestCase;

final class CheckoutOrderHandlerTest extends UnitTestCase
{
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private CheckoutOrderHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->mockRepository(OrderRepository::class);
        $this->productRepository = $this->mockRepository(ProductRepository::class);
        $this->handler = new CheckoutOrderHandler($this->orderRepository, $this->productRepository);
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

        // Mock products with sufficient stock
        $products = [];
        foreach ($order->items() as $item) {
            $product = ProductMother::create(
                id: $item->productId(),
                stock: $item->quantity() + 10 // Ensure sufficient stock
            );
            $products[$item->productId()] = $product;
        }

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn($order);

        $this->productRepository
            ->method('findById')
            ->willReturnCallback(fn($id) => $products[$id] ?? null);

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

        // Mock products with sufficient stock
        $products = [];
        foreach ($order->items() as $item) {
            $product = ProductMother::create(
                id: $item->productId(),
                stock: $item->quantity() + 10
            );
            $products[$item->productId()] = $product;
        }

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn($order);

        $this->productRepository
            ->method('findById')
            ->willReturnCallback(fn($id) => $products[$id] ?? null);

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

    public function test_it_throws_exception_when_insufficient_stock(): void
    {
        // Arrange
        $order = OrderMother::withItems(2);
        $command = new CheckoutOrderCommand(
            orderId: $order->id(),
            customerId: $order->customerId(),
            paymentMethod: 'simulated'
        );

        // Mock products with insufficient stock
        $products = [];
        foreach ($order->items() as $item) {
            $product = ProductMother::create(
                id: $item->productId(),
                stock: $item->quantity() - 1 // Insufficient stock
            );
            $products[$item->productId()] = $product;
        }

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn($order);

        $this->productRepository
            ->method('findById')
            ->willReturnCallback(fn($id) => $products[$id] ?? null);

        // Assert
        $this->expectException(InsufficientStockException::class);

        // Act
        ($this->handler)($command);
    }

    public function test_it_reduces_stock_after_successful_payment(): void
    {
        // Arrange
        $order = OrderMother::withItems(2);
        $command = new CheckoutOrderCommand(
            orderId: $order->id(),
            customerId: $order->customerId(),
            paymentMethod: 'simulated'
        );

        // Mock products with sufficient stock
        $products = [];
        foreach ($order->items() as $item) {
            $product = ProductMother::create(
                id: $item->productId(),
                stock: 50
            );
            $products[$item->productId()] = $product;
        }

        $this->orderRepository
            ->method('findByIdAndCustomer')
            ->willReturn($order);

        $this->productRepository
            ->method('findById')
            ->willReturnCallback(fn($id) => $products[$id] ?? null);

        $this->orderRepository->method('flush');

        // Act
        ($this->handler)($command);

        // Assert - Verify stock was reduced
        foreach ($order->items() as $item) {
            $product = $products[$item->productId()];
            $expectedStock = 50 - $item->quantity();
            $this->assertEquals($expectedStock, $product->stock());
        }
    }
}

