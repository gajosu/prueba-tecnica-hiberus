<?php

declare(strict_types=1);

namespace App\Product\Domain\Repository;

use App\Product\Domain\Entity\Product;
use App\Shared\Domain\Repository\Repository;

interface ProductRepository extends Repository
{
    public function findById(string $id): ?Product;

    public function findAll(int $page = 1, int $limit = 10): array;

    public function search(?string $query, int $page = 1, int $limit = 10): array;

    public function countAll(): int;

    public function countSearch(?string $query): int;
}

