<?php

declare(strict_types=1);

namespace App\Order\Domain\Entity;

use App\Shared\Domain\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Order $order;

    #[ORM\Column(type: 'string', length: 36)]
    private string $productId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $productName;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $unitPrice;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $subtotal;

    public function __construct(
        string $id,
        Order $order,
        string $productId,
        string $productName,
        Money $unitPrice,
        int $quantity
    ) {
        $this->id = $id;
        $this->order = $order;
        $this->productId = $productId;
        $this->productName = $productName;
        $this->unitPrice = $unitPrice->amount();
        $this->currency = $unitPrice->currency();
        $this->quantity = $quantity;
        $this->subtotal = $unitPrice->amount() * $quantity;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function order(): Order
    {
        return $this->order;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function unitPrice(): Money
    {
        return new Money($this->unitPrice, $this->currency);
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function subtotal(): Money
    {
        return new Money($this->subtotal, $this->currency);
    }
}

