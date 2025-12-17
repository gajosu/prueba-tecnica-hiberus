<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Http;

use Symfony\Component\Validator\Constraints as Assert;

final class CheckoutOrderRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Payment method is required')]
        public readonly string $paymentMethod = 'simulated'
    ) {
    }
}

