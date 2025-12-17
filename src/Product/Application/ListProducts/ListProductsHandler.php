<?php

declare(strict_types=1);

namespace App\Product\Application\ListProducts;

use App\Product\Domain\Repository\ProductRepository;

final class ListProductsHandler
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    public function __invoke(ListProductsQuery $query): array
    {
        $products = $this->productRepository->search(
            $query->search,
            $query->page,
            $query->limit
        );

        $total = $this->productRepository->countSearch($query->search);

        return [
            'data' => array_map(fn($product) => [
                'id' => $product->id(),
                'name' => $product->name(),
                'description' => $product->description(),
                'price' => $product->price()->amount(),
                'currency' => $product->price()->currency(),
                'stock' => $product->stock(),
                'image_url' => $product->imageUrl(),
                'active' => $product->isActive(),
                'created_at' => $product->createdAt()->format('Y-m-d H:i:s'),
            ], $products),
            'meta' => [
                'total' => $total,
                'page' => $query->page,
                'limit' => $query->limit,
                'total_pages' => (int) ceil($total / $query->limit),
            ],
        ];
    }
}

