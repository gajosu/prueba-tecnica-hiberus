<?php

declare(strict_types=1);

namespace App\Tests\Shared;

use App\Shared\Infrastructure\Service\FakeUuidGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class UnitTestCase extends TestCase
{
    private ?FakeUuidGenerator $fakeUuidGenerator = null;

    protected function tearDown(): void
    {
        $this->fakeUuidGenerator = null;
        parent::tearDown();
    }

    /**
     * Create a mock for a repository interface
     */
    protected function mockRepository(string $class): MockObject
    {
        return $this->createMock($class);
    }

    /**
     * Assert that a string is a valid UUID
     */
    protected function assertIsUuid(string $value): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value,
            "Value '{$value}' is not a valid UUID"
        );
    }

    /**
     * Get a fake UUID generator for testing
     */
    protected function getFakeUuidGenerator(): FakeUuidGenerator
    {
        if ($this->fakeUuidGenerator === null) {
            $this->fakeUuidGenerator = new FakeUuidGenerator();
        }

        return $this->fakeUuidGenerator;
    }

    /**
     * Assert that a value is a positive integer
     */
    protected function assertIsPositiveInteger(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }

    /**
     * Assert that an array has the expected keys
     */
    protected function assertArrayHasKeys(array $expectedKeys, array $array): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array, "Array does not have key: {$key}");
        }
    }
}

