<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security\EventListener;

use App\Shared\Infrastructure\Security\Attribute\RequiresAuth;
use App\Shared\Infrastructure\Security\Attribute\RequiresRole;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Event listener to check security attributes on controllers
 * Similar to Laravel's middleware system
 */
final class SecurityAttributeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // Skip if not a main request
        if (!$event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();

        // Skip if not a callable
        if (!is_array($controller)) {
            return;
        }

        $controllerObject = $controller[0];
        $method = $controller[1];

        // Get reflection
        $reflectionClass = new \ReflectionClass($controllerObject);
        $reflectionMethod = $reflectionClass->getMethod($method);

        // Check method attributes first, then class attributes
        $attributes = array_merge(
            $reflectionMethod->getAttributes(),
            $reflectionClass->getAttributes()
        );

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            if ($instance instanceof RequiresAuth) {
                if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                    $event->setController(function () {
                        return new JsonResponse(
                            ['error' => 'Authentication required'],
                            Response::HTTP_UNAUTHORIZED
                        );
                    });
                    return;
                }
            }

            if ($instance instanceof RequiresRole) {
                if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
                    $event->setController(function () {
                        return new JsonResponse(
                            ['error' => 'Authentication required'],
                            Response::HTTP_UNAUTHORIZED
                        );
                    });
                    return;
                }

                if (!$this->authorizationChecker->isGranted($instance->role)) {
                    $event->setController(function () use ($instance) {
                        return new JsonResponse(
                            ['error' => sprintf('Role %s required', $instance->role)],
                            Response::HTTP_FORBIDDEN
                        );
                    });
                    return;
                }
            }
        }
    }
}

