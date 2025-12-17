<?php

declare(strict_types=1);

namespace App\Tests\Feature\Auth;

use App\Tests\Feature\FeatureTestCase;

final class LoginTest extends FeatureTestCase
{
    public function test_user_can_login_with_valid_credentials(): void
    {
        // Act
        $this->jsonRequest('POST', '/api/login', [
            'email' => 'customer1@example.com',
            'password' => 'password',
        ]);

        // Assert
        $response = $this->assertJsonResponse(200);

        $this->assertArrayHasKey('customer_id', $response);
        $this->assertArrayHasKey('email', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('role', $response);
        $this->assertArrayHasKey('token', $response);

        $this->assertEquals('customer1@example.com', $response['email']);
        $this->assertEquals('ROLE_USER', $response['role']);
    }

    public function test_admin_can_login(): void
    {
        // Act
        $this->jsonRequest('POST', '/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        // Assert
        $response = $this->assertJsonResponse(200);

        $this->assertEquals('admin@example.com', $response['email']);
        $this->assertEquals('ROLE_ADMIN', $response['role']);
    }

    public function test_login_fails_with_invalid_email(): void
    {
        // Act
        $this->jsonRequest('POST', '/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        // Assert
        $response = $this->assertJsonResponse(401);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid credentials', $response['error']);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        // Act
        $this->jsonRequest('POST', '/api/login', [
            'email' => 'customer1@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response = $this->assertJsonResponse(401);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid credentials', $response['error']);
    }

    public function test_login_requires_email(): void
    {
        // Act
        $this->jsonRequest('POST', '/api/login', [
            'password' => 'password',
        ]);

        // Assert
        $response = $this->assertJsonResponse(400);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Validation failed', $response['error']);
        $this->assertArrayHasKey('violations', $response);
        $this->assertArrayHasKey('email', $response['violations']);
    }

    public function test_login_requires_password(): void
    {
        // Act
        $this->jsonRequest('POST', '/api/login', [
            'email' => 'customer1@example.com',
        ]);

        // Assert
        $response = $this->assertJsonResponse(400);

        $this->assertArrayHasKey('violations', $response);
        $this->assertArrayHasKey('password', $response['violations']);
    }

    public function test_login_validates_email_format(): void
    {
        // Act
        $this->jsonRequest('POST', '/api/login', [
            'email' => 'notanemail',
            'password' => 'password',
        ]);

        // Assert
        $response = $this->assertJsonResponse(400);

        $this->assertArrayHasKey('violations', $response);
        $this->assertArrayHasKey('email', $response['violations']);
        $this->assertStringContainsString('email', strtolower($response['violations']['email']));
    }

    public function test_token_is_jwt_format(): void
    {
        // Act
        $this->jsonRequest('POST', '/api/login', [
            'email' => 'customer1@example.com',
            'password' => 'password',
        ]);

        // Assert
        $response = $this->assertJsonResponse(200);

        $token = $response['token'];

        // Verify JWT format (header.payload.signature)
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'JWT token should have 3 parts');
        $this->assertNotEmpty($parts[0], 'JWT header should not be empty');
        $this->assertNotEmpty($parts[1], 'JWT payload should not be empty');
        $this->assertNotEmpty($parts[2], 'JWT signature should not be empty');
    }
}

