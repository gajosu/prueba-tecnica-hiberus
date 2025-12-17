<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order\Application;

use App\Order\Application\CreateOrder\CreateOrderCommand;
use App\Order\Application\CreateOrder\CreateOrderHandler;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Order\Domain\Repository\OrderRepository;
use App\Product\Domain\Exception\ProductNotFoundException;
use App\Product\Domain\Repository\ProductRepository;
use App\Tests\Shared\Mother\ProductMother;
use App\Tests\Shared\UnitTestCase;

final class CreateOrderHandlerTest extends UnitTestCase
{
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private CreateOrderHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->orderRepository = $this->mockRepository(OrderRepository::class);
        $this->productRepository = $this->mockRepository(ProductRepository::class);
        $this->handler = new CreateOrderHandler(
            $this->orderRepository,
            $this->productRepository,
            $this->getFakeUuidGenerator()
        );
    }

    public function test_it_creates_order_with_valid_items(): void
    {
        // Arrange
        $product = ProductMother::withStock(10);
        $command = new CreateOrderCommand(
            customerId: 'customer-123',
            items: [
                ['productId' => $product->id(), 'quantity' => 2]
            ]
        );

        $this->productRepository
            ->method('findById')
            ->with($product->id())
            ->willReturn($product);

        $this->productRepository->method('save');
        $this->orderRepository->expects($this->once())->method('save');
        $this->orderRepository->expects($this->once())->method('flush');

        // Act
        $orderId = ($this->handler)($command);

        // Assert
        $this->assertIsString($orderId);
    }

    public function test_it_throws_exception_when_product_not_found(): void
    {
        // Arrange
        $command = new CreateOrderCommand(
            customerId: 'customer-123',
            items: [
                ['productId' => 'non-existent', 'quantity' => 1]
            ]
        );

        $this->productRepository
            ->method('findById')
            ->willReturn(null);

        // Assert
        $this->expectException(ProductNotFoundException::class);

        // Act
        ($this->handler)($command);
    }

    public function test_it_throws_exception_when_insufficient_stock(): void
    {
        // Arrange
        $product = ProductMother::withStock(5);
        $command = new CreateOrderCommand(
            customerId: 'customer-123',
            items: [
                ['productId' => $product->id(), 'quantity' => 10]
            ]
        );

        $this->productRepository
            ->method('findById')
            ->willReturn($product);

        // Assert
        $this->expectException(InsufficientStockException::class);

        // Act
        ($this->handler)($command);
    }

    public function test_it_decreases_product_stock(): void
    {
        // Arrange
        $product = ProductMother::withStock(10);
        $initialStock = $product->stock();
        $quantity = 3;

        $command = new CreateOrderCommand(
            customerId: 'customer-123',
            items: [
                ['productId' => $product->id(), 'quantity' => $quantity]
            ]
        );

        $this->productRepository
            ->method('findById')
            ->willReturn($product);

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($prod) use ($initialStock, $quantity) {
                return $prod->stock() === ($initialStock - $quantity);
            }));

        $this->orderRepository->method('save');
        $this->orderRepository->method('flush');

        // Act
        ($this->handler)($command);
    }

    public function test_it_calculates_order_total_correctly(): void
    {
        // Arrange
        $product1 = ProductMother::withStock(10);
        $product2 = ProductMother::withStock(10);

        $command = new CreateOrderCommand(
            customerId: 'customer-123',
            items: [
                ['productId' => $product1->id(), 'quantity' => 2],
                ['productId' => $product2->id(), 'quantity' => 1]
            ]
        );

        $this->productRepository
            ->method('findById')
            ->willReturnMap([
                [$product1->id(), $product1],
                [$product2->id(), $product2]
            ]);

        $this->productRepository->method('save');
        
        $savedOrder = null;
        $this->orderRepository
            ->method('save')
            ->willReturnCallback(function ($order) use (&$savedOrder) {
                $savedOrder = $order;
            });

        $this->orderRepository->method('flush');

        // Act
        ($this->handler)($command);

        // Assert
        $expectedTotal = ($product1->price()->amount() * 2) + $product2->price()->amount();
        $this->assertEquals($expectedTotal, $savedOrder->total()->amount());
    }
}

