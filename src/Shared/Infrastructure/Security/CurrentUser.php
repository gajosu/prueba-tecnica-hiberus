<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use App\Customer\Domain\Entity\Customer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Service to get the current authenticated user
 * Similar to Laravel's Auth::user()
 */
final class CurrentUser
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function get(): ?Customer
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        return $user instanceof Customer ? $user : null;
    }

    public function getOrFail(): Customer
    {
        $user = $this->get();
        if (!$user) {
            throw new \RuntimeException('User not authenticated');
        }

        return $user;
    }

    public function id(): ?string
    {
        return $this->get()?->id();
    }

    public function email(): ?string
    {
        return $this->get()?->email();
    }

    public function isAdmin(): bool
    {
        $user = $this->get();
        return $user && $user->role() === 'ADMIN';
    }

    public function isCustomer(): bool
    {
        $user = $this->get();
        return $user && $user->role() === 'CUSTOMER';
    }
}

