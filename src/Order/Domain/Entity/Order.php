<?php

declare(strict_types=1);

namespace App\Order\Domain\Entity;

use App\Order\Domain\ValueObject\OrderStatus;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $customerId;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist', 'remove'])]
    private Collection $items;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $total;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $paidAt;

    public function __construct(
        string $id,
        string $customerId,
        string $currency = 'EUR'
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->items = new ArrayCollection();
        $this->status = OrderStatus::PENDING->value;
        $this->total = 0;
        $this->currency = $currency;
        $this->createdAt = new DateTimeImmutable();
        $this->paidAt = null;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function items(): Collection
    {
        return $this->items;
    }

    public function status(): OrderStatus
    {
        return OrderStatus::from($this->status);
    }

    public function total(): Money
    {
        return new Money($this->total, $this->currency);
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function paidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items->add($item);
        $this->recalculateTotal();
    }

    public function markAsPaid(): void
    {
        if (!$this->status()->canBePaid()) {
            throw new \DomainException('Order cannot be paid in current status');
        }

        $this->status = OrderStatus::PAID->value;
        $this->paidAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (!$this->status()->isPending()) {
            throw new \DomainException('Only pending orders can be cancelled');
        }

        $this->status = OrderStatus::CANCELLED->value;
    }

    public function belongsToCustomer(string $customerId): bool
    {
        return $this->customerId === $customerId;
    }

    private function recalculateTotal(): void
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->subtotal()->amount();
        }
        $this->total = $total;
    }
}

