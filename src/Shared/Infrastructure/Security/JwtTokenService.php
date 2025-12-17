<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use App\Customer\Domain\Entity\Customer;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JwtTokenService
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function generateToken(Customer $customer): string
    {
        return $this->jwtManager->create($customer);
    }
}

