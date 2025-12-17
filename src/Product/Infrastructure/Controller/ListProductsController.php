<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Controller;

use App\Product\Application\ListProducts\ListProductsHandler;
use App\Product\Application\ListProducts\ListProductsQuery;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ListProductsController
{
    public function __construct(
        private readonly ListProductsHandler $handler
    ) {
    }

    #[Route('/api/products', name: 'api_products_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/products',
        summary: 'List all products with pagination',
        tags: ['Products']
    )]
    #[OA\Parameter(
        name: 'search',
        in: 'query',
        description: 'Search term for product name',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Items per page',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Parameter(
        name: 'sort',
        in: 'query',
        description: 'Sort field',
        required: false,
        schema: new OA\Schema(type: 'string', default: 'created_at')
    )]
    #[OA\Response(
        response: 200,
        description: 'List of products',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string', example: 'product-123'),
                            new OA\Property(property: 'name', type: 'string', example: 'Laptop Dell XPS 13'),
                            new OA\Property(property: 'description', type: 'string'),
                            new OA\Property(property: 'price', type: 'number', example: 1299.99),
                            new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
                            new OA\Property(property: 'stock', type: 'integer', example: 50)
                        ]
                    )
                ),
                new OA\Property(
                    property: 'meta',
                    properties: [
                        new OA\Property(property: 'total', type: 'integer'),
                        new OA\Property(property: 'page', type: 'integer'),
                        new OA\Property(property: 'limit', type: 'integer'),
                        new OA\Property(property: 'total_pages', type: 'integer')
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $search = $request->query->get('search');
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 10);
            $sort = $request->query->get('sort', 'created_at');

            $query = new ListProductsQuery(
                search: $search,
                page: $page,
                limit: $limit,
                sort: $sort
            );

            $result = ($this->handler)($query);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

