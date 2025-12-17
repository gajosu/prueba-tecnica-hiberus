<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Http;

use Symfony\Component\Validator\Constraints as Assert;

final class LoginRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email format')]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 3, minMessage: 'Password must be at least 3 characters')]
        public readonly string $password
    ) {
    }
}

