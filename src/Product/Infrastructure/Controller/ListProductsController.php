<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Controller;

use App\Product\Application\ListProducts\ListProductsHandler;
use App\Product\Application\ListProducts\ListProductsQuery;
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

