<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Application;

use App\Product\Application\CreateProduct\CreateProductCommand;
use App\Product\Application\CreateProduct\CreateProductHandler;
use App\Product\Domain\Repository\ProductRepository;
use App\Tests\Shared\UnitTestCase;

final class CreateProductHandlerTest extends UnitTestCase
{
    private ProductRepository $productRepository;
    private CreateProductHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->mockRepository(ProductRepository::class);
        $this->handler = new CreateProductHandler(
            $this->productRepository,
            $this->getFakeUuidGenerator()
        );
    }

    public function test_it_creates_product_with_valid_data(): void
    {
        // Arrange
        $command = new CreateProductCommand(
            name: 'Test Product',
            description: 'Test Description',
            price: 99.99,
            currency: 'EUR',
            stock: 10
        );

        $this->productRepository
            ->expects($this->once())
            ->method('save');

        $this->productRepository
            ->expects($this->once())
            ->method('flush');

        // Act
        $productId = ($this->handler)($command);

        // Assert
        $this->assertIsString($productId);
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $productId);
    }

    public function test_it_generates_unique_id_for_product(): void
    {
        // Arrange
        $command = new CreateProductCommand(
            name: 'Product',
            description: null,
            price: 10.0,
            currency: 'EUR',
            stock: 5
        );

        $this->productRepository->method('save');
        $this->productRepository->method('flush');

        // Act
        $firstId = ($this->handler)($command);
        $secondId = ($this->handler)($command);

        // Assert
        $this->assertNotEquals($firstId, $secondId);
    }

    public function test_it_persists_product_to_repository(): void
    {
        // Arrange
        $command = new CreateProductCommand(
            name: 'Product',
            description: 'Description',
            price: 50.0,
            currency: 'EUR',
            stock: 20
        );

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($product) use ($command) {
                return $product->name() === $command->name
                    && $product->description() === $command->description
                    && $product->stock() === $command->stock;
            }));

        $this->productRepository
            ->expects($this->once())
            ->method('flush');

        // Act
        ($this->handler)($command);
    }

    public function test_it_creates_product_with_correct_price(): void
    {
        // Arrange
        $command = new CreateProductCommand(
            name: 'Product',
            description: null,
            price: 99.99,
            currency: 'USD',
            stock: 5
        );

        $savedProduct = null;
        $this->productRepository
            ->method('save')
            ->willReturnCallback(function ($product) use (&$savedProduct) {
                $savedProduct = $product;
            });

        $this->productRepository->method('flush');

        // Act
        ($this->handler)($command);

        // Assert
        $this->assertNotNull($savedProduct);
        $this->assertEquals(99.99, $savedProduct->price()->amount());
        $this->assertEquals('USD', $savedProduct->price()->currency());
    }
}

