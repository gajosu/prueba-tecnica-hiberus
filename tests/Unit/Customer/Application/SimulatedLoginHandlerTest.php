<?php

declare(strict_types=1);

namespace App\Tests\Unit\Customer\Application;

use App\Customer\Application\SimulatedLogin\SimulatedLoginCommand;
use App\Customer\Application\SimulatedLogin\SimulatedLoginHandler;
use App\Customer\Domain\Repository\CustomerRepository;
use App\Tests\Shared\Mother\CustomerMother;
use App\Tests\Shared\UnitTestCase;
use InvalidArgumentException;

final class SimulatedLoginHandlerTest extends UnitTestCase
{
    private CustomerRepository $customerRepository;
    private SimulatedLoginHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customerRepository = $this->mockRepository(CustomerRepository::class);
        $this->handler = new SimulatedLoginHandler($this->customerRepository);
    }

    public function test_it_authenticates_valid_customer(): void
    {
        // Arrange
        $customer = CustomerMother::customer();
        $command = new SimulatedLoginCommand(
            customerId: $customer->id(),
            role: 'CUSTOMER'
        );

        $this->customerRepository
            ->expects($this->once())
            ->method('findById')
            ->with($customer->id())
            ->willReturn($customer);

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertArrayHasKeys(['customer_id', 'email', 'name', 'role', 'token'], $result);
        $this->assertEquals($customer->id(), $result['customer_id']);
        $this->assertEquals($customer->email(), $result['email']);
    }

    public function test_it_throws_exception_for_invalid_customer(): void
    {
        // Arrange
        $command = new SimulatedLoginCommand(
            customerId: 'non-existent',
            role: 'CUSTOMER'
        );

        $this->customerRepository
            ->method('findById')
            ->willReturn(null);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer not found');

        // Act
        ($this->handler)($command);
    }

    public function test_it_validates_customer_role(): void
    {
        // Arrange
        $customer = CustomerMother::customer();
        $command = new SimulatedLoginCommand(
            customerId: $customer->id(),
            role: 'ADMIN' // Customer has CUSTOMER role, not ADMIN
        );

        $this->customerRepository
            ->method('findById')
            ->willReturn($customer);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role for this customer');

        // Act
        ($this->handler)($command);
    }

    public function test_it_generates_token(): void
    {
        // Arrange
        $customer = CustomerMother::admin();
        $command = new SimulatedLoginCommand(
            customerId: $customer->id(),
            role: 'ADMIN'
        );

        $this->customerRepository
            ->method('findById')
            ->willReturn($customer);

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertArrayHasKey('token', $result);
        $this->assertIsString($result['token']);
        $this->assertNotEmpty($result['token']);
    }

    public function test_it_authenticates_admin_user(): void
    {
        // Arrange
        $admin = CustomerMother::admin();
        $command = new SimulatedLoginCommand(
            customerId: $admin->id(),
            role: 'ADMIN'
        );

        $this->customerRepository
            ->method('findById')
            ->willReturn($admin);

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertEquals('ADMIN', $result['role']);
    }
}

