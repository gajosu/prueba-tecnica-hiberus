<?php

declare(strict_types=1);

namespace App\Order\Domain\Repository;

use App\Order\Domain\Entity\Order;
use App\Shared\Domain\Repository\Repository;

interface OrderRepository extends Repository
{
    public function findById(string $id): ?Order;

    public function findByIdAndCustomer(string $id, string $customerId): ?Order;

    public function findByCustomer(string $customerId): array;
}

