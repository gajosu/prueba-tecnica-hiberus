<?php

declare(strict_types=1);

namespace App\Shared\Domain\Repository;

interface Repository
{
    public function save(object $entity): void;
    
    public function remove(object $entity): void;
    
    public function flush(): void;
}

