<?php

declare(strict_types=1);

namespace App\Product\Application\CreateProduct;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Shared\Domain\Service\UuidGenerator;
use App\Shared\Domain\ValueObject\Money;

final class CreateProductHandler
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly UuidGenerator $uuidGenerator
    ) {
    }

    public function __invoke(CreateProductCommand $command): string
    {
        $productId = $this->uuidGenerator->generate();
        $price = new Money($command->price, $command->currency);

        $product = new Product(
            $productId,
            $command->name,
            $command->description,
            $price,
            $command->stock
        );

        $this->productRepository->save($product);
        $this->productRepository->flush();

        return $productId;
    }
}

