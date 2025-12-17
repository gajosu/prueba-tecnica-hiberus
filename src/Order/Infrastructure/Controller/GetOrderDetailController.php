<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Controller;

use App\Order\Application\GetOrderDetail\GetOrderDetailHandler;
use App\Order\Application\GetOrderDetail\GetOrderDetailQuery;
use App\Order\Domain\Exception\OrderNotFoundException;
use App\Shared\Infrastructure\Security\Attribute\RequiresRole;
use App\Shared\Infrastructure\Security\CurrentUser;
use OpenApi\Attributes as OA;
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
    #[OA\Get(
        path: '/api/orders/{id}',
        summary: 'Get order details',
        description: 'Get details of an order. Users can only see their own orders.',
        security: [['Bearer' => []]],
        tags: ['Orders']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Order ID',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Order details',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string', example: 'order-123'),
                new OA\Property(property: 'customer_id', type: 'string'),
                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                new OA\Property(property: 'total', type: 'number', example: 1299.99),
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'product_id', type: 'string'),
                            new OA\Property(property: 'product_name', type: 'string'),
                            new OA\Property(property: 'quantity', type: 'integer'),
                            new OA\Property(property: 'price', type: 'number')
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Order not found')]
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

