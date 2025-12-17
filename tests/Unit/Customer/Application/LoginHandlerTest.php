<?php

declare(strict_types=1);

namespace App\Tests\Unit\Customer\Application;

use App\Customer\Application\Login\LoginCommand;
use App\Customer\Application\Login\LoginHandler;
use App\Customer\Domain\Repository\CustomerRepository;
use App\Shared\Infrastructure\Security\JwtTokenService;
use App\Tests\Shared\Mother\CustomerMother;
use App\Tests\Shared\UnitTestCase;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginHandlerTest extends UnitTestCase
{
    private CustomerRepository $customerRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private JwtTokenService $jwtTokenService;
    private LoginHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerRepository = $this->mockRepository(CustomerRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->jwtTokenService = $this->createMock(JwtTokenService::class);
        $this->handler = new LoginHandler($this->customerRepository, $this->passwordHasher, $this->jwtTokenService);
    }

    public function test_it_authenticates_valid_customer_with_password(): void
    {
        // Arrange
        $customer = CustomerMother::customer();
        $command = new LoginCommand(
            email: $customer->email(),
            password: 'password'
        );

        $this->customerRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($customer->email())
            ->willReturn($customer);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($customer, 'password')
            ->willReturn(true);

        $this->jwtTokenService
            ->expects($this->once())
            ->method('generateToken')
            ->with($customer)
            ->willReturn('fake.jwt.token');

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertArrayHasKeys(['customer_id', 'email', 'name', 'role', 'token'], $result);
        $this->assertEquals($customer->id(), $result['customer_id']);
        $this->assertEquals($customer->email(), $result['email']);
        $this->assertEquals('ROLE_USER', $result['role']);
    }

    public function test_it_throws_exception_for_nonexistent_email(): void
    {
        // Arrange
        $command = new LoginCommand(
            email: 'nonexistent@example.com',
            password: 'password'
        );

        $this->customerRepository
            ->method('findByEmail')
            ->willReturn(null);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credentials');

        // Act
        ($this->handler)($command);
    }

    public function test_it_throws_exception_for_invalid_password(): void
    {
        // Arrange
        $customer = CustomerMother::customer();
        $command = new LoginCommand(
            email: $customer->email(),
            password: 'wrongpassword'
        );

        $this->customerRepository
            ->method('findByEmail')
            ->willReturn($customer);

        $this->passwordHasher
            ->method('isPasswordValid')
            ->with($customer, 'wrongpassword')
            ->willReturn(false);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credentials');

        // Act
        ($this->handler)($command);
    }

    public function test_it_generates_jwt_token(): void
    {
        // Arrange
        $customer = CustomerMother::customer();
        $command = new LoginCommand(
            email: $customer->email(),
            password: 'password'
        );

        $this->customerRepository
            ->method('findByEmail')
            ->willReturn($customer);

        $this->passwordHasher
            ->method('isPasswordValid')
            ->willReturn(true);

        $this->jwtTokenService
            ->expects($this->once())
            ->method('generateToken')
            ->with($customer)
            ->willReturn('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.test.token');

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertArrayHasKey('token', $result);
        $this->assertIsString($result['token']);
        $this->assertNotEmpty($result['token']);
        $this->assertStringStartsWith('eyJ', $result['token']); // JWT format
    }

    public function test_it_authenticates_admin_user(): void
    {
        // Arrange
        $admin = CustomerMother::admin();
        $command = new LoginCommand(
            email: $admin->email(),
            password: 'password'
        );

        $this->customerRepository
            ->method('findByEmail')
            ->willReturn($admin);

        $this->passwordHasher
            ->method('isPasswordValid')
            ->willReturn(true);

        $this->jwtTokenService
            ->method('generateToken')
            ->willReturn('fake.jwt.token');

        // Act
        $result = ($this->handler)($command);

        // Assert
        $this->assertEquals('ROLE_ADMIN', $result['role']);
        $this->assertEquals($admin->email(), $result['email']);
    }
}
