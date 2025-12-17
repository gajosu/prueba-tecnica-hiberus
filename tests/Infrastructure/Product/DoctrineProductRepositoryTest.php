<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Product;

use App\Product\Domain\Entity\Product;
use App\Product\Infrastructure\Persistence\DoctrineProductRepository;
use App\Tests\Shared\InfrastructureTestCase;
use App\Tests\Shared\Mother\ProductMother;

final class DoctrineProductRepositoryTest extends InfrastructureTestCase
{
    private DoctrineProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $registry = self::bootKernel()->getContainer()->get('doctrine');
        $this->repository = new DoctrineProductRepository($registry);
    }

    public function test_it_saves_a_product(): void
    {
        // Arrange
        $product = ProductMother::random();

        // Act
        $this->repository->save($product);
        $this->repository->flush();

        // Assert
        $foundProduct = $this->repository->findById($product->id());
        $this->assertNotNull($foundProduct);
        $this->assertEquals($product->id(), $foundProduct->id());
        $this->assertEquals($product->name(), $foundProduct->name());
    }

    public function test_it_finds_product_by_id(): void
    {
        // Arrange
        $product = ProductMother::random();
        $this->persistAndFlush($product);
        $this->clearEntityManager();

        // Act
        $foundProduct = $this->repository->findById($product->id());

        // Assert
        $this->assertNotNull($foundProduct);
        $this->assertEquals($product->id(), $foundProduct->id());
        $this->assertEquals($product->name(), $foundProduct->name());
    }

    public function test_it_returns_null_when_product_not_found(): void
    {
        // Act
        $product = $this->repository->findById('non-existent-id');

        // Assert
        $this->assertNull($product);
    }

    public function test_it_lists_all_products_paginated(): void
    {
        // Arrange
        $this->persistAndFlush(ProductMother::random());
        $this->persistAndFlush(ProductMother::random());
        $this->persistAndFlush(ProductMother::random());

        // Act
        $products = $this->repository->findAll(page: 1, limit: 2);

        // Assert
        $this->assertCount(2, $products);
        $this->assertInstanceOf(Product::class, $products[0]);
    }

    public function test_it_searches_products_by_name(): void
    {
        // Arrange - Usar término de búsqueda único
        $uniqueTerm = 'UniqueProduct' . uniqid();
        $product1 = ProductMother::withName($uniqueTerm . ' Laptop XPS');
        $product2 = ProductMother::withName('iPhone 13 Pro');
        $product3 = ProductMother::withName($uniqueTerm . ' Monitor');

        $this->persistAndFlush($product1);
        $this->persistAndFlush($product2);
        $this->persistAndFlush($product3);

        // Act
        $results = $this->repository->search($uniqueTerm);

        // Assert
        $this->assertCount(2, $results);
    }

    public function test_it_counts_products(): void
    {
        // Arrange
        $countBefore = $this->repository->countAll();

        $this->persistAndFlush(ProductMother::random());
        $this->persistAndFlush(ProductMother::random());
        $this->persistAndFlush(ProductMother::inactive()); // Should not be counted (inactive)

        // Act
        $countAfter = $this->repository->countAll();

        // Assert - Verificar que aumentó en 2
        $this->assertEquals($countBefore + 2, $countAfter);
    }

    public function test_it_only_returns_active_products(): void
    {
        // Arrange
        $activeProduct = ProductMother::random();
        $inactiveProduct = ProductMother::inactive();

        $this->persistAndFlush($activeProduct);
        $this->persistAndFlush($inactiveProduct);
        $this->clearEntityManager();

        // Act - findAll() debe devolver solo productos activos
        $allProducts = $this->repository->findAll(page: 1, limit: 1000);

        // Assert - Verificar que TODOS los productos son activos
        $this->assertGreaterThan(0, count($allProducts));

        foreach ($allProducts as $product) {
            $this->assertTrue($product->isActive(),
                "Product {$product->id()} should be active but is not");
        }

        // Verificar que nuestro producto inactivo NO está en la lista
        $productIds = array_map(fn($p) => $p->id(), $allProducts);
        $this->assertNotContains($inactiveProduct->id(), $productIds,
            "Inactive product should not be in findAll() results");
    }
}

