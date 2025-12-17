<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Http;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateProductRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Product name is required')]
        #[Assert\Length(min: 3, max: 255, minMessage: 'Name must be at least 3 characters')]
        public readonly string $name,

        #[Assert\Length(max: 1000)]
        public readonly ?string $description = null,

        #[Assert\NotBlank(message: 'Price is required')]
        #[Assert\Positive(message: 'Price must be greater than 0')]
        #[Assert\Type(type: 'float', message: 'Price must be a valid number')]
        public readonly float $price = 0,

        #[Assert\Length(exactly: 3, exactMessage: 'Currency must be a 3-letter code')]
        public readonly string $currency = 'EUR',

        #[Assert\PositiveOrZero(message: 'Stock cannot be negative')]
        #[Assert\Type(type: 'integer', message: 'Stock must be an integer')]
        public readonly int $stock = 0,

        #[Assert\Url(requireTld: false, message: 'Image URL must be a valid URL')]
        #[Assert\Length(max: 500)]
        public readonly ?string $imageUrl = null
    ) {
    }
}

