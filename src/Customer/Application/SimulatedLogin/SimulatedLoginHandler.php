<?php

declare(strict_types=1);

namespace App\Customer\Application\SimulatedLogin;

use App\Customer\Domain\Repository\CustomerRepository;

final class SimulatedLoginHandler
{
    public function __construct(
        private readonly CustomerRepository $customerRepository
    ) {
    }

    public function __invoke(SimulatedLoginCommand $command): array
    {
        // Simulated authentication - no password verification
        $customer = $this->customerRepository->findById($command->customerId);

        if (!$customer) {
            throw new \InvalidArgumentException('Customer not found');
        }

        // Verify role matches
        if ($customer->role() !== $command->role) {
            throw new \InvalidArgumentException('Invalid role for this customer');
        }

        return [
            'customer_id' => $customer->id(),
            'email' => $customer->email(),
            'name' => $customer->name(),
            'role' => $customer->role(),
            'token' => $this->generateSimulatedToken($customer->id(), $customer->role()),
        ];
    }

    private function generateSimulatedToken(string $customerId, string $role): string
    {
        // Simulated token generation - in real app use JWT or similar
        return base64_encode(json_encode([
            'customer_id' => $customerId,
            'role' => $role,
            'expires_at' => time() + 3600, // 1 hour
        ]));
    }
}

