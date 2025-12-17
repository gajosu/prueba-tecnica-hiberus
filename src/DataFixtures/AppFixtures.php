<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Customer\Domain\Entity\Customer;
use App\Product\Domain\Entity\Product;
use App\Shared\Domain\ValueObject\Money;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create customers with hashed passwords
        // Password for all users: "password"

        // Create Admin
        $adminCustomer = new Customer(
            id: 'admin-001',
            email: 'admin@example.com',
            name: 'Admin User',
            role: 'ROLE_ADMIN'
        );
        $adminCustomer->setPassword(
            $this->passwordHasher->hashPassword($adminCustomer, 'password')
        );
        $manager->persist($adminCustomer);

        // Create Customer 1
        $customer1 = new Customer(
            id: 'customer-001',
            email: 'customer1@example.com',
            name: 'John Doe',
            role: 'ROLE_USER'
        );
        $customer1->setPassword(
            $this->passwordHasher->hashPassword($customer1, 'password')
        );
        $manager->persist($customer1);

        // Create Customer 2
        $customer2 = new Customer(
            id: 'customer-002',
            email: 'customer2@example.com',
            name: 'Jane Smith',
            role: 'ROLE_USER'
        );
        $customer2->setPassword(
            $this->passwordHasher->hashPassword($customer2, 'password')
        );
        $manager->persist($customer2);

        // Create products
        $products = [
            ['name' => 'Laptop Dell XPS 13', 'description' => 'High-performance laptop', 'price' => 1299.99, 'stock' => 50],
            ['name' => 'iPhone 15 Pro', 'description' => 'Latest Apple smartphone', 'price' => 999.99, 'stock' => 100],
            ['name' => 'Samsung Galaxy S24', 'description' => 'Flagship Android phone', 'price' => 899.99, 'stock' => 75],
            ['name' => 'MacBook Pro 16"', 'description' => 'Professional laptop for developers', 'price' => 2499.99, 'stock' => 30],
            ['name' => 'iPad Air', 'description' => 'Versatile tablet', 'price' => 599.99, 'stock' => 60],
            ['name' => 'AirPods Pro', 'description' => 'Wireless earbuds with noise cancellation', 'price' => 249.99, 'stock' => 200],
            ['name' => 'Sony WH-1000XM5', 'description' => 'Premium noise-cancelling headphones', 'price' => 399.99, 'stock' => 40],
            ['name' => 'LG 27" 4K Monitor', 'description' => 'Ultra HD display', 'price' => 449.99, 'stock' => 25],
            ['name' => 'Logitech MX Master 3', 'description' => 'Ergonomic wireless mouse', 'price' => 99.99, 'stock' => 150],
            ['name' => 'Mechanical Keyboard', 'description' => 'RGB mechanical gaming keyboard', 'price' => 129.99, 'stock' => 80],
        ];

        foreach ($products as $productData) {
            $product = new Product(
                id: uniqid('product-'),
                name: $productData['name'],
                description: $productData['description'],
                price: new Money($productData['price'], 'EUR'),
                stock: $productData['stock']
            );
            $manager->persist($product);
        }

        $manager->flush();
    }
}

