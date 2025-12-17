<?php

declare(strict_types=1);

namespace App\Tests\Shared\Mother;

use App\Product\Domain\Entity\Product;
use App\Shared\Domain\ValueObject\Money;
use Faker\Factory;
use Faker\Generator;

final class ProductMother
{
    private static ?Generator $faker = null;

    private static function faker(): Generator
    {
        if (self::$faker === null) {
            self::$faker = Factory::create();
        }

        return self::$faker;
    }

    public static function create(
        ?string $id = null,
        ?string $name = null,
        ?string $description = null,
        ?Money $price = null,
        ?int $stock = null,
        ?string $imageUrl = null,
        ?bool $active = null
    ): Product {
        return new Product(
            $id ?? self::faker()->uuid(),
            $name ?? self::faker()->words(3, true),
            $description ?? self::faker()->sentence(),
            $price ?? MoneyMother::random(),
            $stock ?? self::faker()->numberBetween(0, 100),
            $imageUrl ?? 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&q=80',
            $active ?? true
        );
    }

    public static function random(): Product
    {
        return self::create();
    }

    public static function withoutStock(): Product
    {
        return self::create(stock: 0);
    }

    public static function inactive(): Product
    {
        return self::create(active: false);
    }

    public static function withId(string $id): Product
    {
        return self::create(id: $id);
    }

    public static function withName(string $name): Product
    {
        return self::create(name: $name);
    }

    public static function withPrice(Money $price): Product
    {
        return self::create(price: $price);
    }

    public static function withStock(int $stock): Product
    {
        return self::create(stock: $stock);
    }
}

