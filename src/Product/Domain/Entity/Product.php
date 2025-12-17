<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $price;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'integer')]
    private int $stock;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $imageUrl;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $name,
        ?string $description,
        Money $price,
        int $stock,
        ?string $imageUrl = null,
        bool $active = true
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price->amount();
        $this->currency = $price->currency();
        $this->stock = $stock;
        $this->imageUrl = $imageUrl;
        $this->active = $active;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function price(): Money
    {
        return new Money($this->price, $this->currency);
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function imageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateInfo(string $name, ?string $description, Money $price): void
    {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price->amount();
        $this->currency = $price->currency();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateStock(int $stock): void
    {
        if ($stock < 0) {
            throw new \InvalidArgumentException('Stock cannot be negative');
        }
        $this->stock = $stock;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function decreaseStock(int $quantity): void
    {
        if ($quantity > $this->stock) {
            throw new \DomainException("Insufficient stock for product {$this->name}");
        }
        $this->stock -= $quantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function increaseStock(int $quantity): void
    {
        $this->stock += $quantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->active = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function hasStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }
}

