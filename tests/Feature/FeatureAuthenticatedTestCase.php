<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Base test case for authenticated feature tests
 * Provides authentication helpers
 */
abstract class FeatureAuthenticatedTestCase extends FeatureTestCase
{
    protected function loginAsCustomer(string $email = 'customer1@example.com', string $password = 'password'): string
    {
        $this->jsonRequest('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        return $response['token'] ?? '';
    }

    protected function loginAsAdmin(string $email = 'admin@example.com', string $password = 'password'): string
    {
        return $this->loginAsCustomer($email, $password);
    }

    protected function authenticatedJsonRequest(string $method, string $uri, array $data = [], ?string $token = null): void
    {
        if ($token === null) {
            $token = $this->loginAsCustomer();
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode($data)
        );
    }

    protected function authenticatedGet(string $uri, ?string $token = null): void
    {
        if ($token === null) {
            $token = $this->loginAsCustomer();
        }

        $this->client->request(
            'GET',
            $uri,
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]
        );
    }
}

