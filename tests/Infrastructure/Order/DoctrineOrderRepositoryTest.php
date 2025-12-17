<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Order;

use App\Order\Domain\Entity\Order;
use App\Order\Infrastructure\Persistence\DoctrineOrderRepository;
use App\Tests\Shared\InfrastructureTestCase;
use App\Tests\Shared\Mother\OrderItemMother;
use App\Tests\Shared\Mother\OrderMother;

final class DoctrineOrderRepositoryTest extends InfrastructureTestCase
{
    private DoctrineOrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $registry = self::bootKernel()->getContainer()->get('doctrine');
        $this->repository = new DoctrineOrderRepository($registry);
    }

    public function test_it_saves_an_order_with_items(): void
    {
        // Arrange
        $order = OrderMother::random();
        $order->addItem(OrderItemMother::random($order));
        $order->addItem(OrderItemMother::random($order));

        // Act
        $this->repository->save($order);
        $this->repository->flush();

        // Assert
        $foundOrder = $this->repository->findById($order->id());
        $this->assertNotNull($foundOrder);
        $this->assertEquals($order->id(), $foundOrder->id());
        $this->assertCount(2, $foundOrder->items());
    }

    public function test_it_finds_order_by_id(): void
    {
        // Arrange
        $order = OrderMother::random();
        $this->persistAndFlush($order);
        $this->clearEntityManager();

        // Act
        $foundOrder = $this->repository->findById($order->id());

        // Assert
        $this->assertNotNull($foundOrder);
        $this->assertEquals($order->id(), $foundOrder->id());
        $this->assertEquals($order->customerId(), $foundOrder->customerId());
    }

    public function test_it_finds_order_by_id_and_customer(): void
    {
        // Arrange
        $customerId = 'customer-123';
        $order = OrderMother::withCustomerId($customerId);
        $this->persistAndFlush($order);
        $this->clearEntityManager();

        // Act
        $foundOrder = $this->repository->findByIdAndCustomer($order->id(), $customerId);

        // Assert
        $this->assertNotNull($foundOrder);
        $this->assertEquals($order->id(), $foundOrder->id());
        $this->assertEquals($customerId, $foundOrder->customerId());
    }

    public function test_it_returns_null_when_order_not_found_for_customer(): void
    {
        // Arrange
        $order = OrderMother::withCustomerId('customer-1');
        $this->persistAndFlush($order);

        // Act
        $foundOrder = $this->repository->findByIdAndCustomer($order->id(), 'customer-2');

        // Assert
        $this->assertNull($foundOrder);
    }

    public function test_it_finds_orders_by_customer(): void
    {
        // Arrange - Usar ID único para evitar conflictos con otros tests
        $customerId = 'customer-unique-' . uniqid();
        $order1 = OrderMother::withCustomerId($customerId);
        $order2 = OrderMother::withCustomerId($customerId);
        $order3 = OrderMother::withCustomerId('other-customer-' . uniqid());

        $this->persistAndFlush($order1);
        $this->persistAndFlush($order2);
        $this->persistAndFlush($order3);
        $this->clearEntityManager();

        // Act
        $orders = $this->repository->findByCustomer($customerId);

        // Assert
        $this->assertCount(2, $orders);
        $this->assertContainsOnlyInstancesOf(Order::class, $orders);
    }

    public function test_it_persists_order_status_changes(): void
    {
        // Arrange
        $order = OrderMother::random();
        $this->persistAndFlush($order);
        $this->clearEntityManager();

        // Act
        $foundOrder = $this->repository->findById($order->id());
        $foundOrder->markAsPaid();
        $this->repository->flush();
        $this->clearEntityManager();

        // Assert
        $updatedOrder = $this->repository->findById($order->id());
        $this->assertTrue($updatedOrder->status()->isPaid());
        $this->assertNotNull($updatedOrder->paidAt());
    }

    public function test_it_cascades_order_item_deletion(): void
    {
        // Arrange
        $order = OrderMother::random();
        $order->addItem(OrderItemMother::random($order));
        $this->persistAndFlush($order);
        $orderId = $order->id();
        $this->clearEntityManager();

        // Act
        $managedOrder = $this->repository->findById($orderId);
        $this->repository->remove($managedOrder);
        $this->repository->flush();
        $this->clearEntityManager();

        // Assert
        $deletedOrder = $this->repository->findById($orderId);
        $this->assertNull($deletedOrder);
    }
}

