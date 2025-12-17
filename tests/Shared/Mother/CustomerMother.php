<?php

declare(strict_types=1);

namespace App\Tests\Shared\Mother;

use App\Customer\Domain\Entity\Customer;
use Faker\Factory;
use Faker\Generator;

final class CustomerMother
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
        ?string $email = null,
        ?string $name = null,
        ?string $role = null
    ): Customer {
        return new Customer(
            $id ?? self::faker()->uuid(),
            $email ?? self::faker()->email(),
            $name ?? self::faker()->name(),
            $role ?? 'CUSTOMER'
        );
    }

    public static function random(): Customer
    {
        return self::create();
    }

    public static function admin(): Customer
    {
        return self::create(
            email: 'admin-' . uniqid() . '@example.com',
            name: 'Admin User',
            role: 'ADMIN'
        );
    }

    public static function customer(): Customer
    {
        return self::create(
            email: 'customer-' . uniqid() . '@example.com',
            name: 'Customer User',
            role: 'CUSTOMER'
        );
    }

    public static function withId(string $id): Customer
    {
        return self::create(id: $id);
    }

    public static function withEmail(string $email): Customer
    {
        return self::create(email: $email);
    }

    public static function withRole(string $role): Customer
    {
        return self::create(role: $role);
    }
}

