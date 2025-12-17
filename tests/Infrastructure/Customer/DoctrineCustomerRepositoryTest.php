<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Customer;

use App\Customer\Infrastructure\Persistence\DoctrineCustomerRepository;
use App\Tests\Shared\InfrastructureTestCase;
use App\Tests\Shared\Mother\CustomerMother;

final class DoctrineCustomerRepositoryTest extends InfrastructureTestCase
{
    private DoctrineCustomerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $registry = self::bootKernel()->getContainer()->get('doctrine');
        $this->repository = new DoctrineCustomerRepository($registry);
    }

    public function test_it_saves_a_customer(): void
    {
        // Arrange
        $customer = CustomerMother::random();

        // Act
        $this->repository->save($customer);
        $this->repository->flush();

        // Assert
        $foundCustomer = $this->repository->findById($customer->id());
        $this->assertNotNull($foundCustomer);
        $this->assertEquals($customer->id(), $foundCustomer->id());
        $this->assertEquals($customer->email(), $foundCustomer->email());
    }

    public function test_it_finds_customer_by_id(): void
    {
        // Arrange
        $customer = CustomerMother::random();
        $this->persistAndFlush($customer);
        $this->clearEntityManager();

        // Act
        $foundCustomer = $this->repository->findById($customer->id());

        // Assert
        $this->assertNotNull($foundCustomer);
        $this->assertEquals($customer->id(), $foundCustomer->id());
        $this->assertEquals($customer->name(), $foundCustomer->name());
    }

    public function test_it_finds_customer_by_email(): void
    {
        // Arrange
        $email = 'test-' . uniqid() . '@example.com';
        $customer = CustomerMother::withEmail($email);
        $this->persistAndFlush($customer);
        $this->clearEntityManager();

        // Act
        $foundCustomer = $this->repository->findByEmail($email);

        // Assert
        $this->assertNotNull($foundCustomer);
        $this->assertEquals($email, $foundCustomer->email());
        $this->assertEquals($customer->id(), $foundCustomer->id());
    }

    public function test_it_returns_null_when_customer_not_found(): void
    {
        // Act
        $customer = $this->repository->findById('non-existent-id');

        // Assert
        $this->assertNull($customer);
    }

    public function test_it_returns_null_when_email_not_found(): void
    {
        // Act
        $customer = $this->repository->findByEmail('nonexistent@example.com');

        // Assert
        $this->assertNull($customer);
    }

    public function test_it_persists_customer_role(): void
    {
        // Arrange
        $adminCustomer = CustomerMother::admin();
        $this->persistAndFlush($adminCustomer);
        $this->clearEntityManager();

        // Act
        $foundCustomer = $this->repository->findById($adminCustomer->id());

        // Assert
        $this->assertTrue($foundCustomer->isAdmin());
        $this->assertFalse($foundCustomer->isCustomer());
    }
}

