<?php

declare(strict_types=1);

namespace App\Product\Application\CreateProduct;

final class CreateProductCommand
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly float $price,
        public readonly string $currency,
        public readonly int $stock,
        public readonly ?string $imageUrl = null
    ) {
    }
}

