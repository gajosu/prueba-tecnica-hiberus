<?php

declare(strict_types=1);

namespace App\Tests\Shared\Mother;

use App\Order\Domain\Entity\Order;
use Faker\Factory;
use Faker\Generator;

final class OrderMother
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
        ?string $customerId = null,
        array $items = [],
        ?string $currency = null
    ): Order {
        $order = new Order(
            $id ?? self::faker()->uuid(),
            $customerId ?? self::faker()->uuid(),
            $currency ?? 'EUR'
        );

        foreach ($items as $item) {
            $order->addItem($item);
        }

        return $order;
    }

    public static function random(): Order
    {
        return self::create();
    }

    public static function withItems(int $count): Order
    {
        $order = self::create();

        for ($i = 0; $i < $count; $i++) {
            $order->addItem(OrderItemMother::random($order));
        }

        return $order;
    }

    public static function withCustomerId(string $customerId): Order
    {
        return self::create(customerId: $customerId);
    }

    public static function withId(string $id): Order
    {
        return self::create(id: $id);
    }
}

