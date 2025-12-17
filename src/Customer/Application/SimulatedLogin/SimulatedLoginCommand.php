<?php

declare(strict_types=1);

namespace App\Customer\Application\SimulatedLogin;

final class SimulatedLoginCommand
{
    public function __construct(
        public readonly string $customerId,
        public readonly string $role = 'CUSTOMER'
    ) {
    }
}

