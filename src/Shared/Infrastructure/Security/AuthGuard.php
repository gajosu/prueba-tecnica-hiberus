<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Guard to check if user is authenticated
 * Similar to Laravel's 'auth' middleware
 */
final class AuthGuard
{
    use SecurityGuardTrait;

    public function check(): void
    {
        if (!$this->isAuthenticated()) {
            throw new AccessDeniedException('Authentication required');
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
                Response::HTTP_UNAUTHORIZED
            );
        }
    }
}

