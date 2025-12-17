<?php

declare(strict_types=1);

namespace App\Tests\Feature;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FeatureTestCase extends WebTestCase
{
    protected ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown();
        $this->client = static::createClient(['environment' => 'test', 'debug' => true]);
    }

    protected function tearDown(): void
    {
        $this->client = null;
        parent::tearDown();
    }

    protected function jsonRequest(string $method, string $uri, array $data = []): void
    {
        $this->client->request(
            $method,
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    protected function assertJsonResponse(int $expectedStatusCode): array
    {
        $response = $this->client->getResponse();
        $content = $response->getContent();

        $this->assertEquals(
            $expectedStatusCode,
            $response->getStatusCode(),
            sprintf(
                'Expected status code %d, got %d. Response: %s',
                $expectedStatusCode,
                $response->getStatusCode(),
                $content
            )
        );

        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            'Response is not JSON'
        );

        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded, 'Response content is not valid JSON');

        return $decoded;
    }

}

