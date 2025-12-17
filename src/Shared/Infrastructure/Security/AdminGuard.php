<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Guard to check if user has ADMIN role
 * Similar to Laravel's middleware guard
 */
final class AdminGuard
{
    use SecurityGuardTrait;

    public function check(): void
    {
        if (!$this->isAuthenticated()) {
            throw new AccessDeniedException('Authentication required');
        }

        if (!$this->hasRole('ADMIN')) {
            throw new AccessDeniedException('Admin access required');
        }
    }

    public function checkAndRespond(): ?JsonResponse
    {
        try {
            $this->check();
            return null;
        } catch (AccessDeniedException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_FORBIDDEN
            );
        }
    }
}

