<?php

declare(strict_types=1);

namespace App\Product\Application\ListProducts;

final class ListProductsQuery
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly int $page = 1,
        public readonly int $limit = 10,
        public readonly ?string $sort = 'created_at'
    ) {
    }
}

