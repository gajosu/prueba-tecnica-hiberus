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

        // Create 20 products with real images from Unsplash
        $products = [
            [
                'name' => 'Laptop Dell XPS 13',
                'description' => 'High-performance ultrabook with 11th gen Intel Core i7, 16GB RAM, and stunning InfinityEdge display',
                'price' => 1299.99,
                'stock' => 50,
                'imageUrl' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=800&q=80'
            ],
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest Apple smartphone with A17 Pro chip, titanium design, and advanced camera system',
                'price' => 999.99,
                'stock' => 100,
                'imageUrl' => 'https://images.unsplash.com/photo-1678685888221-cda773a3dcdb?w=800&q=80'
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Flagship Android phone with 200MP camera, S Pen, and powerful Snapdragon processor',
                'price' => 899.99,
                'stock' => 75,
                'imageUrl' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=800&q=80'
            ],
            [
                'name' => 'MacBook Pro 16"',
                'description' => 'Professional laptop with M3 Max chip, stunning Liquid Retina XDR display, perfect for developers',
                'price' => 2499.99,
                'stock' => 30,
                'imageUrl' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=80'
            ],
            [
                'name' => 'iPad Air M2',
                'description' => 'Versatile tablet with M2 chip, Apple Pencil support, ideal for creativity and productivity',
                'price' => 599.99,
                'stock' => 60,
                'imageUrl' => 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=800&q=80'
            ],
            [
                'name' => 'AirPods Pro 2',
                'description' => 'Wireless earbuds with active noise cancellation, spatial audio, and all-day battery life',
                'price' => 249.99,
                'stock' => 200,
                'imageUrl' => 'https://images.unsplash.com/photo-1606841837239-c5a1a4a07af7?w=800&q=80'
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Premium noise-cancelling over-ear headphones with exceptional sound quality and comfort',
                'price' => 399.99,
                'stock' => 40,
                'imageUrl' => 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=800&q=80'
            ],
            [
                'name' => 'LG 27" 4K UltraFine',
                'description' => 'Ultra HD IPS display with HDR support, perfect for creative professionals',
                'price' => 449.99,
                'stock' => 25,
                'imageUrl' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=800&q=80'
            ],
            [
                'name' => 'Logitech MX Master 3S',
                'description' => 'Ergonomic wireless mouse with ultra-precise scrolling and multi-device connectivity',
                'price' => 99.99,
                'stock' => 150,
                'imageUrl' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=800&q=80'
            ],
            [
                'name' => 'Keychron K8 Pro',
                'description' => 'RGB mechanical gaming keyboard with hot-swappable switches and wireless connectivity',
                'price' => 129.99,
                'stock' => 80,
                'imageUrl' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=800&q=80'
            ],
            [
                'name' => 'Sony A7 IV Camera',
                'description' => 'Professional mirrorless camera with 33MP sensor, 4K 60fps video, and advanced autofocus',
                'price' => 2499.99,
                'stock' => 15,
                'imageUrl' => 'https://images.unsplash.com/photo-1606980286107-cf17bb0e1f3e?w=800&q=80'
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'description' => 'Gaming console with vibrant 7-inch OLED screen and enhanced audio',
                'price' => 349.99,
                'stock' => 120,
                'imageUrl' => 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=800&q=80'
            ],
            [
                'name' => 'Apple Watch Series 9',
                'description' => 'Advanced smartwatch with fitness tracking, health monitoring, and always-on Retina display',
                'price' => 399.99,
                'stock' => 90,
                'imageUrl' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=800&q=80'
            ],
            [
                'name' => 'DJI Mini 3 Pro Drone',
                'description' => 'Compact drone with 4K HDR video, 34-min flight time, and obstacle avoidance',
                'price' => 759.99,
                'stock' => 35,
                'imageUrl' => 'https://images.unsplash.com/photo-1473968512647-3e447244af8f?w=800&q=80'
            ],
            [
                'name' => 'Samsung 65" QLED TV',
                'description' => '4K Quantum Dot TV with HDR10+, gaming mode, and smart TV features',
                'price' => 1299.99,
                'stock' => 20,
                'imageUrl' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&q=80'
            ],
            [
                'name' => 'Bose QuietComfort 45',
                'description' => 'Premium wireless headphones with world-class noise cancellation and 24-hour battery',
                'price' => 329.99,
                'stock' => 55,
                'imageUrl' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&q=80'
            ],
            [
                'name' => 'Kindle Paperwhite',
                'description' => 'E-reader with 6.8" glare-free display, waterproof design, and weeks of battery life',
                'price' => 139.99,
                'stock' => 180,
                'imageUrl' => 'https://images.unsplash.com/photo-1592855878070-cb80b5c4c513?w=800&q=80'
            ],
            [
                'name' => 'GoPro HERO12 Black',
                'description' => 'Action camera with 5.3K video, HyperSmooth stabilization, and waterproof design',
                'price' => 399.99,
                'stock' => 45,
                'imageUrl' => 'https://images.unsplash.com/photo-1519337265831-281ec6cc8514?w=800&q=80'
            ],
            [
                'name' => 'Anker PowerCore 20K',
                'description' => 'High-capacity portable charger with fast charging and multiple device support',
                'price' => 49.99,
                'stock' => 300,
                'imageUrl' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=800&q=80'
            ],
            [
                'name' => 'Razer DeathAdder V3',
                'description' => 'Pro-grade gaming mouse with 30K DPI sensor, lightweight design, and RGB lighting',
                'price' => 69.99,
                'stock' => 110,
                'imageUrl' => 'https://images.unsplash.com/photo-1563297007-0686b7003af7?w=800&q=80'
            ],
        ];

        foreach ($products as $productData) {
            $product = new Product(
                id: uniqid('product-'),
                name: $productData['name'],
                description: $productData['description'],
                price: new Money($productData['price'], 'EUR'),
                stock: $productData['stock'],
                imageUrl: $productData['imageUrl']
            );
            $manager->persist($product);
        }

        $manager->flush();
    }
}

