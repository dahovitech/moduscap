<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private int $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $unitPrice = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $optionsPrice = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $totalPrice = '0.00';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $customizationNotes = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $selectedOptions = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->updatedAt = new \DateTime();
        $this->calculateTotal();
        return $this;
    }

    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;
        $this->updatedAt = new \DateTime();
        $this->calculateTotal();
        return $this;
    }

    public function getOptionsPrice(): string
    {
        return $this->optionsPrice;
    }

    public function setOptionsPrice(string $optionsPrice): static
    {
        $this->optionsPrice = $optionsPrice;
        $this->updatedAt = new \DateTime();
        $this->calculateTotal();
        return $this;
    }

    public function getTotalPrice(): string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    public function getCustomizationNotes(): ?string
    {
        return $this->customizationNotes;
    }

    public function setCustomizationNotes(?string $customizationNotes): static
    {
        $this->customizationNotes = $customizationNotes;
        return $this;
    }

    public function getSelectedOptions(): ?array
    {
        return $this->selectedOptions;
    }

    public function setSelectedOptions(?array $selectedOptions): static
    {
        $this->selectedOptions = $selectedOptions;
        $this->updatedAt = new \DateTime();
        $this->calculateTotal();
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        if ($product) {
            $this->unitPrice = $product->getBasePrice() ?? '0.00';
            $this->calculateTotal();
        }
        return $this;
    }

    /**
     * Calculate total price (unit price + options price) * quantity
     */
    private function calculateTotal(): void
    {
        $baseTotal = (floatval($this->unitPrice) + floatval($this->optionsPrice)) * $this->quantity;
        $this->totalPrice = number_format($baseTotal, 2, '.', '');
    }

    /**
     * Add option with its price
     */
    public function addOption(ProductOption $option): void
    {
        if (!$this->selectedOptions) {
            $this->selectedOptions = [];
        }
        
        $this->selectedOptions[$option->getCode()] = [
            'id' => $option->getId(),
            'name' => $option->getName(),
            'price' => $option->getPrice(),
            'code' => $option->getCode()
        ];
        
        $this->calculateTotal();
    }

    /**
     * Remove option
     */
    public function removeOption(ProductOption $option): void
    {
        if ($this->selectedOptions && isset($this->selectedOptions[$option->getCode()])) {
            unset($this->selectedOptions[$option->getCode()]);
            $this->calculateTotal();
        }
    }

    /**
     * Get options total price
     */
    public function getOptionsTotalPrice(): string
    {
        $total = 0;
        if ($this->selectedOptions) {
            foreach ($this->selectedOptions as $optionData) {
                $total += floatval($optionData['price'] ?? 0);
            }
        }
        return number_format($total, 2, '.', '');
    }

    /**
     * Check if product has specific option
     */
    public function hasOption(ProductOption $option): bool
    {
        return $this->selectedOptions && isset($this->selectedOptions[$option->getCode()]);
    }

    /**
     * Get product name for this order item
     */
    public function getProductName(?string $locale = null): string
    {
        return $this->product?->getName($locale) ?: '';
    }

    /**
     * Get product code for this order item
     */
    public function getProductCode(): string
    {
        return $this->product?->getCode() ?: '';
    }

    public function __toString(): string
    {
        return $this->getProductName() . ' (x' . $this->quantity . ')';
    }
}