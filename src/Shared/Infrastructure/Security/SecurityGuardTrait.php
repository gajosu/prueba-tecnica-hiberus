<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use App\Customer\Domain\Entity\Customer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Trait to provide common security check methods
 */
trait SecurityGuardTrait
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    protected function isAuthenticated(): bool
    {
        return $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY');
    }

    protected function hasRole(string $role): bool
    {
        return $this->authorizationChecker->isGranted($role);
    }

    protected function getUser(): ?Customer
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        return $user instanceof Customer ? $user : null;
    }

    protected function getUserOrFail(): Customer
    {
        $user = $this->getUser();
        if (!$user) {
            throw new \RuntimeException('User not authenticated');
        }

        return $user;
    }
}

