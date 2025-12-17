<?php

declare(strict_types=1);

namespace App\Tests\Shared\Mother;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Entity\OrderItem;
use App\Shared\Domain\ValueObject\Money;
use Faker\Factory;
use Faker\Generator;

final class OrderItemMother
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
        ?Order $order = null,
        ?string $productId = null,
        ?string $productName = null,
        ?Money $unitPrice = null,
        ?int $quantity = null
    ): OrderItem {
        return new OrderItem(
            $id ?? self::faker()->uuid(),
            $order ?? OrderMother::random(),
            $productId ?? self::faker()->uuid(),
            $productName ?? self::faker()->words(3, true),
            $unitPrice ?? MoneyMother::random(),
            $quantity ?? self::faker()->numberBetween(1, 5)
        );
    }

    public static function random(Order $order): OrderItem
    {
        return self::create(order: $order);
    }

    public static function withProduct(Order $order, string $productId, string $productName): OrderItem
    {
        return self::create(
            order: $order,
            productId: $productId,
            productName: $productName
        );
    }

    public static function withQuantity(Order $order, int $quantity): OrderItem
    {
        return self::create(order: $order, quantity: $quantity);
    }

    public static function withPrice(Order $order, Money $price): OrderItem
    {
        return self::create(order: $order, unitPrice: $price);
    }
}

