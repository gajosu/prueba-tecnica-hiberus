<?php

declare(strict_types=1);

namespace App\Order\Domain\ValueObject;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function canBePaid(): bool
    {
        return $this === self::PENDING;
    }
}

