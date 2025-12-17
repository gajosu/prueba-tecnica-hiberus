<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Controller;

use App\Order\Application\GetOrderDetail\GetOrderDetailHandler;
use App\Order\Application\GetOrderDetail\GetOrderDetailQuery;
use App\Order\Domain\Exception\OrderNotFoundException;
use App\Shared\Infrastructure\Security\Attribute\RequiresRole;
use App\Shared\Infrastructure\Security\CurrentUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[RequiresRole('ROLE_USER')]
final class GetOrderDetailController
{
    public function __construct(
        private readonly GetOrderDetailHandler $handler,
        private readonly CurrentUser $currentUser
    ) {
    }

    #[Route('/api/orders/{id}', name: 'api_orders_detail', methods: ['GET'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        try {
            // Get customer ID from authenticated user
            $customerId = $this->currentUser->id();

            $query = new GetOrderDetailQuery(
                orderId: $id,
                customerId: $customerId
            );

            $result = ($this->handler)($query);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (OrderNotFoundException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

