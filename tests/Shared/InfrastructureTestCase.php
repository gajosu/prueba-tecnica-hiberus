<?php

declare(strict_types=1);

namespace App\Tests\Shared;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class InfrastructureTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Iniciar transacción para rollback al final
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback de la transacción para limpiar cambios
        $connection = $this->entityManager->getConnection();
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $this->entityManager->close();
        parent::tearDown();
    }

    /**
     * Clear all data from database tables
     */
    protected function clearDatabase(): void
    {
        $connection = $this->entityManager->getConnection();

        // Disable foreign key checks
        $connection->executeStatement('SET CONSTRAINTS ALL DEFERRED');

        // Truncate all tables
        $tables = ['order_items', 'orders', 'products', 'customers'];
        foreach ($tables as $table) {
            $connection->executeStatement("TRUNCATE TABLE {$table} CASCADE");
        }

        // Re-enable foreign key checks
        $connection->executeStatement('SET CONSTRAINTS ALL IMMEDIATE');
    }

    /**
     * Get a repository by class name
     */
    protected function getRepository(string $class): object
    {
        return $this->entityManager->getRepository($class);
    }

    /**
     * Persist and flush an entity
     */
    protected function persistAndFlush(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Clear the entity manager to detach all entities
     */
    protected function clearEntityManager(): void
    {
        $this->entityManager->clear();
    }
}

