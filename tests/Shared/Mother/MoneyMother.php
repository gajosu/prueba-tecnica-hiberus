<?php

declare(strict_types=1);

namespace App\Tests\Shared\Mother;

use App\Shared\Domain\ValueObject\Money;
use Faker\Factory;
use Faker\Generator;

final class MoneyMother
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
        ?float $amount = null,
        ?string $currency = null
    ): Money {
        return new Money(
            $amount ?? self::faker()->randomFloat(2, 1, 1000),
            $currency ?? 'EUR'
        );
    }

    public static function random(): Money
    {
        return self::create();
    }

    public static function zero(): Money
    {
        return new Money(0, 'EUR');
    }

    public static function euros(float $amount): Money
    {
        return new Money($amount, 'EUR');
    }

    public static function dollars(float $amount): Money
    {
        return new Money($amount, 'USD');
    }

    public static function withAmount(float $amount): Money
    {
        return new Money($amount, 'EUR');
    }
}

