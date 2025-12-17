<?php

declare(strict_types=1);

namespace App\Customer\Domain\Repository;

use App\Customer\Domain\Entity\Customer;
use App\Shared\Domain\Repository\Repository;

interface CustomerRepository extends Repository
{
    public function findById(string $id): ?Customer;

    public function findByEmail(string $email): ?Customer;
}

