<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Application;

use App\Product\Application\CreateProduct\CreateProductCommand;
use App\Product\Application\CreateProduct\CreateProductHandler;
use App\Product\Domain\Repository\ProductRepository;
use App\Shared\Infrastructure\Service\FakeUuidGenerator;
use PHPUnit\Framework\TestCase;

final class CreateProductHandlerTest extends TestCase
{
    private ProductRepository $productRepository;
    private FakeUuidGenerator $uuidGenerator;
    private CreateProductHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->uuidGenerator = new FakeUuidGenerator();
        $this->handler = new CreateProductHandler(
            $this->productRepository,
            $this->uuidGenerator
        );
    }

    public function test_it_creates_a_product_with_generated_uuid(): void
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
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $productId);
    }

    public function test_it_generates_sequential_uuids(): void
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
        $thirdId = ($this->handler)($command);

        // Assert
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $firstId);
        $this->assertEquals('00000000-0000-0000-0000-000000000002', $secondId);
        $this->assertEquals('00000000-0000-0000-0000-000000000003', $thirdId);
    }

    public function test_it_can_use_fixed_uuid(): void
    {
        // Arrange
        $fixedUuid = 'fixed-uuid-for-testing-123';
        $this->uuidGenerator->setFixedUuid($fixedUuid);

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
        $productId = ($this->handler)($command);

        // Assert
        $this->assertEquals($fixedUuid, $productId);
    }
}

