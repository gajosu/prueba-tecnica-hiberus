<?php

declare(strict_types=1);

namespace App\Tests\Feature\Order;

use App\Tests\Feature\FeatureAuthenticatedTestCase;

final class OrderTest extends FeatureAuthenticatedTestCase
{
    private function getFirstProductId(): string
    {
        $this->client->request('GET', '/api/products?limit=1');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        return $response['data'][0]['id'];
    }

    public function test_can_create_order(): void
    {
        // Arrange
        $productId = $this->getFirstProductId();

        // Act
        $this->authenticatedJsonRequest('POST', '/api/orders', [
            'items' => [
                [
                    'productId' => $productId,
                    'quantity' => 1,
                ],
            ],
        ]);

        // Assert
        $response = $this->assertJsonResponse(201);

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertNotEmpty($response['id']);
    }

    public function test_create_order_requires_authentication(): void
    {
        // Arrange
        $productId = $this->getFirstProductId();

        // Act - Try without authentication
        $this->jsonRequest('POST', '/api/orders', [
            'items' => [
                ['productId' => $productId, 'quantity' => 1],
            ],
        ]);

        // Assert
        $response = $this->assertJsonResponse(401);

        // JWT Authentication returns a message, not error
        $this->assertTrue(
            isset($response['error']) || isset($response['message']) || isset($response['code']),
            'Response should contain error, message or code'
        );
    }

    public function test_create_order_requires_items(): void
    {
        // Act
        $this->authenticatedJsonRequest('POST', '/api/orders', [
            'items' => [],
        ]);

        // Assert
        $response = $this->assertJsonResponse(400);

        $this->assertArrayHasKey('violations', $response);
        $this->assertArrayHasKey('items', $response['violations']);
    }

    public function test_can_get_order_detail(): void
    {
        // Arrange - Create an order first
        $productId = $this->getFirstProductId();
        $token = $this->loginAsCustomer();

        $this->authenticatedJsonRequest('POST', '/api/orders', [
            'items' => [['productId' => $productId, 'quantity' => 1]],
        ], $token);

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        // Act
        $this->authenticatedGet("/api/orders/{$orderId}", $token);

        // Assert
        $response = $this->assertJsonResponse(200);

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('customer_id', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('currency', $response);
        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('created_at', $response);

        $this->assertEquals($orderId, $response['id']);
        $this->assertEquals('pending', $response['status']);
    }

    public function test_cannot_get_order_from_another_customer(): void
    {
        // Arrange - Create an order with customer 1
        $productId = $this->getFirstProductId();
        $customer1Token = $this->loginAsCustomer('customer1@example.com');

        $this->authenticatedJsonRequest('POST', '/api/orders', [
            'items' => [['productId' => $productId, 'quantity' => 1]],
        ], $customer1Token);

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        // Act - Try to access with customer 2
        $customer2Token = $this->loginAsCustomer('customer2@example.com');
        $this->authenticatedGet("/api/orders/{$orderId}", $customer2Token);

        // Assert - Order should not be found (404) since it belongs to another customer
        $response = $this->assertJsonResponse(404);

        $this->assertArrayHasKey('error', $response);
    }

    public function test_can_checkout_order(): void
    {
        // Arrange - Create an order
        $productId = $this->getFirstProductId();
        $token = $this->loginAsCustomer();

        $this->authenticatedJsonRequest('POST', '/api/orders', [
            'items' => [['productId' => $productId, 'quantity' => 1]],
        ], $token);

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        // Act
        $this->authenticatedJsonRequest('POST', "/api/orders/{$orderId}/checkout", [
            'paymentMethod' => 'simulated',
        ], $token);

        // Assert
        $response = $this->assertJsonResponse(200);

        $this->assertArrayHasKey('order_id', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('paid_at', $response);
        $this->assertArrayHasKey('message', $response);

        $this->assertEquals('paid', $response['status']);
        $this->assertNotNull($response['paid_at']);
    }

    public function test_order_items_have_correct_structure(): void
    {
        // Arrange
        $productId = $this->getFirstProductId();
        $token = $this->loginAsCustomer();

        $this->authenticatedJsonRequest('POST', '/api/orders', [
            'items' => [['productId' => $productId, 'quantity' => 2]],
        ], $token);

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $orderId = $createResponse['id'];

        // Act
        $this->authenticatedGet("/api/orders/{$orderId}", $token);

        // Assert
        $response = $this->assertJsonResponse(200);

        $this->assertIsArray($response['items']);
        $this->assertGreaterThan(0, count($response['items']));

        $item = $response['items'][0];
        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('product_id', $item);
        $this->assertArrayHasKey('product_name', $item);
        $this->assertArrayHasKey('unit_price', $item);
        $this->assertArrayHasKey('quantity', $item);
        $this->assertArrayHasKey('subtotal', $item);

        $this->assertEquals(2, $item['quantity']);
    }

    public function test_admin_can_access_user_endpoints(): void
    {
        // Arrange - Admin creates an order
        $productId = $this->getFirstProductId();
        $adminToken = $this->loginAsAdmin();

        // Act - Admin can create order (user endpoint)
        $this->authenticatedJsonRequest('POST', '/api/orders', [
            'items' => [['productId' => $productId, 'quantity' => 1]],
        ], $adminToken);

        // Assert
        $response = $this->assertJsonResponse(201);
        $this->assertArrayHasKey('id', $response);
        $orderId = $response['id'];

        // Act - Admin can view their own order
        $this->authenticatedGet("/api/orders/{$orderId}", $adminToken);

        // Assert
        $response = $this->assertJsonResponse(200);
        $this->assertEquals($orderId, $response['id']);

        // Act - Admin can checkout order
        $this->authenticatedJsonRequest('POST', "/api/orders/{$orderId}/checkout", [
            'paymentMethod' => 'simulated',
        ], $adminToken);

        // Assert
        $response = $this->assertJsonResponse(200);
        $this->assertEquals('paid', $response['status']);
    }
}

