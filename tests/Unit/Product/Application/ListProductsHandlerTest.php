<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Application;

use App\Product\Application\ListProducts\ListProductsHandler;
use App\Product\Application\ListProducts\ListProductsQuery;
use App\Product\Domain\Repository\ProductRepository;
use App\Tests\Shared\Mother\ProductMother;
use App\Tests\Shared\UnitTestCase;

final class ListProductsHandlerTest extends UnitTestCase
{
    private ProductRepository $productRepository;
    private ListProductsHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productRepository = $this->mockRepository(ProductRepository::class);
        $this->handler = new ListProductsHandler($this->productRepository);
    }

    public function test_it_lists_products_with_pagination(): void
    {
        // Arrange
        $query = new ListProductsQuery(page: 1, limit: 10);
        $products = [ProductMother::random(), ProductMother::random()];

        $this->productRepository
            ->expects($this->once())
            ->method('search')
            ->with(null, 1, 10)
            ->willReturn($products);

        $this->productRepository
            ->expects($this->once())
            ->method('countSearch')
            ->with(null)
            ->willReturn(2);

        // Act
        $result = ($this->handler)($query);

        // Assert
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(2, $result['data']);
    }

    public function test_it_searches_products_by_query(): void
    {
        // Arrange
        $query = new ListProductsQuery(search: 'laptop', page: 1, limit: 10);
        $products = [ProductMother::withName('Laptop Dell')];

        $this->productRepository
            ->expects($this->once())
            ->method('search')
            ->with('laptop', 1, 10)
            ->willReturn($products);

        $this->productRepository
            ->method('countSearch')
            ->willReturn(1);

        // Act
        $result = ($this->handler)($query);

        // Assert
        $this->assertCount(1, $result['data']);
    }

    public function test_it_returns_correct_metadata(): void
    {
        // Arrange
        $query = new ListProductsQuery(page: 2, limit: 5);
        
        $this->productRepository
            ->method('search')
            ->willReturn([]);

        $this->productRepository
            ->method('countSearch')
            ->willReturn(15);

        // Act
        $result = ($this->handler)($query);

        // Assert
        $this->assertEquals(15, $result['meta']['total']);
        $this->assertEquals(2, $result['meta']['page']);
        $this->assertEquals(5, $result['meta']['limit']);
        $this->assertEquals(3, $result['meta']['total_pages']);
    }

    public function test_it_returns_product_details_in_response(): void
    {
        // Arrange
        $query = new ListProductsQuery();
        $product = ProductMother::random();

        $this->productRepository
            ->method('search')
            ->willReturn([$product]);

        $this->productRepository
            ->method('countSearch')
            ->willReturn(1);

        // Act
        $result = ($this->handler)($query);

        // Assert
        $productData = $result['data'][0];
        $this->assertArrayHasKeys(['id', 'name', 'description', 'price', 'currency', 'stock', 'active', 'created_at'], $productData);
        $this->assertEquals($product->id(), $productData['id']);
        $this->assertEquals($product->name(), $productData['name']);
    }
}

