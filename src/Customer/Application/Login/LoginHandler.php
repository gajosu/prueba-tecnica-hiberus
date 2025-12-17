<?php

declare(strict_types=1);

namespace App\Customer\Application\Login;

use App\Customer\Domain\Repository\CustomerRepository;
use App\Shared\Infrastructure\Security\JwtTokenService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginHandler
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JwtTokenService $jwtTokenService
    ) {
    }

    public function __invoke(LoginCommand $command): array
    {
        // Find customer by email
        $customer = $this->customerRepository->findByEmail($command->email);

        if (!$customer) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        // Verify password using Symfony's PasswordHasher
        if (!$this->passwordHasher->isPasswordValid($customer, $command->password)) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        return [
            'customer_id' => $customer->id(),
            'email' => $customer->email(),
            'name' => $customer->name(),
            'role' => $customer->role(),
            'token' => $this->jwtTokenService->generateToken($customer),
        ];
    }
}
