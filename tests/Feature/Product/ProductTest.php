<?php

declare(strict_types=1);

namespace App\Tests\Feature\Product;

use App\Tests\Feature\FeatureAuthenticatedTestCase;

final class ProductTest extends FeatureAuthenticatedTestCase
{
    public function test_can_list_products(): void
    {
        // Act
        $this->client->request('GET', '/api/products');

        // Assert
        $response = $this->assertJsonResponse(200);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertIsArray($response['data']);

        // Verify meta structure
        $this->assertArrayHasKey('total', $response['meta']);
        $this->assertArrayHasKey('page', $response['meta']);
        $this->assertArrayHasKey('limit', $response['meta']);
        $this->assertArrayHasKey('total_pages', $response['meta']);
    }

    public function test_can_list_products_with_pagination(): void
    {
        // Act
        $this->client->request('GET', '/api/products?page=1&limit=5');

        // Assert
        $response = $this->assertJsonResponse(200);

        $this->assertLessThanOrEqual(5, count($response['data']));
        $this->assertEquals(1, $response['meta']['page']);
        $this->assertEquals(5, $response['meta']['limit']);
    }

    public function test_can_search_products_by_name(): void
    {
        // Act
        $this->client->request('GET', '/api/products?search=iPhone');

        // Assert
        $response = $this->assertJsonResponse(200);

        $this->assertArrayHasKey('data', $response);

        // Verify all products contain the search term
        foreach ($response['data'] as $product) {
            $this->assertTrue(
                stripos($product['name'], 'iPhone') !== false ||
                stripos($product['description'] ?? '', 'iPhone') !== false,
                'Product should contain search term in name or description'
            );
        }
    }

    public function test_can_create_product(): void
    {
        // Arrange - Login as admin
        $token = $this->loginAsAdmin();

        // Act
        $this->authenticatedJsonRequest('POST', '/api/products', [
            'name' => 'New Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'stock' => 50,
            'currency' => 'EUR',
        ], $token);

        // Assert
        $response = $this->assertJsonResponse(201);

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertNotEmpty($response['id']);
    }

    public function test_create_product_requires_name(): void
    {
        // Arrange - Login as admin
        $token = $this->loginAsAdmin();

        // Act
        $this->authenticatedJsonRequest('POST', '/api/products', [
            'price' => 99.99,
            'stock' => 50,
        ], $token);

        // Assert
        $response = $this->assertJsonResponse(400);

        $this->assertArrayHasKey('violations', $response);
        $this->assertArrayHasKey('name', $response['violations']);
    }

    public function test_create_product_requires_price(): void
    {
        // Arrange - Login as admin
        $token = $this->loginAsAdmin();

        // Act
        $this->authenticatedJsonRequest('POST', '/api/products', [
            'name' => 'Test Product',
            'stock' => 50,
        ], $token);

        // Assert
        $response = $this->assertJsonResponse(400);

        $this->assertArrayHasKey('violations', $response);
        $this->assertArrayHasKey('price', $response['violations']);
    }

    public function test_create_product_validates_price_positive(): void
    {
        // Arrange - Login as admin
        $token = $this->loginAsAdmin();

        // Act
        $this->authenticatedJsonRequest('POST', '/api/products', [
            'name' => 'Test Product',
            'price' => -10,
            'stock' => 50,
        ], $token);

        // Assert
        $response = $this->assertJsonResponse(400);

        $this->assertArrayHasKey('violations', $response);
        $this->assertArrayHasKey('price', $response['violations']);
    }

    public function test_create_product_validates_stock_not_negative(): void
    {
        // Arrange - Login as admin
        $token = $this->loginAsAdmin();

        // Act
        $this->authenticatedJsonRequest('POST', '/api/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => -5,
        ], $token);

        // Assert
        $response = $this->assertJsonResponse(400);

        $this->assertArrayHasKey('violations', $response);
        $this->assertArrayHasKey('stock', $response['violations']);
    }

    public function test_product_list_shows_correct_structure(): void
    {
        // Act
        $this->client->request('GET', '/api/products?limit=1');

        // Assert
        $response = $this->assertJsonResponse(200);

        if (count($response['data']) > 0) {
            $product = $response['data'][0];

            $this->assertArrayHasKey('id', $product);
            $this->assertArrayHasKey('name', $product);
            $this->assertArrayHasKey('description', $product);
            $this->assertArrayHasKey('price', $product);
            $this->assertArrayHasKey('currency', $product);
            $this->assertArrayHasKey('stock', $product);
            $this->assertArrayHasKey('active', $product);
            $this->assertArrayHasKey('created_at', $product);
        }
    }
}

