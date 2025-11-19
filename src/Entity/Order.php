<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\NotBlank]
    private string $orderNumber;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_PAID, self::STATUS_PROCESSING, self::STATUS_SHIPPED, self::STATUS_DELIVERED, self::STATUS_CANCELLED])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $subtotal = '0.00';

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private string $total = '0.00';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $approvedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $clientNotes = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $clientName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $clientEmail = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $clientPhone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $clientAddress = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $paymentProof = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'order.user.required')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $approvedBy = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->orderNumber = $this->generateOrderNumber();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();
        
        if ($status === self::STATUS_APPROVED && !$this->approvedAt) {
            $this->approvedAt = new \DateTime();
        } elseif ($status === self::STATUS_PAID && !$this->paidAt) {
            $this->paidAt = new \DateTime();
        }
        
        return $this;
    }

    public function getSubtotal(): string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getTotal(): string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;
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

    public function getPaidAt(): ?\DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeInterface $paidAt): static
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    public function getApprovedAt(): ?\DateTimeInterface
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeInterface $approvedAt): static
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function getClientNotes(): ?string
    {
        return $this->clientNotes;
    }

    public function setClientNotes(?string $clientNotes): static
    {
        $this->clientNotes = $clientNotes;
        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): static
    {
        $this->clientName = $clientName;
        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(?string $clientEmail): static
    {
        $this->clientEmail = $clientEmail;
        return $this;
    }

    public function getClientPhone(): ?string
    {
        return $this->clientPhone;
    }

    public function setClientPhone(?string $clientPhone): static
    {
        $this->clientPhone = $clientPhone;
        return $this;
    }

    public function getClientAddress(): ?string
    {
        return $this->clientAddress;
    }

    public function setClientAddress(?string $clientAddress): static
    {
        $this->clientAddress = $clientAddress;
        return $this;
    }

    public function getPaymentProof(): ?string
    {
        return $this->paymentProof;
    }

    public function setPaymentProof(?string $paymentProof): static
    {
        $this->paymentProof = $paymentProof;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?User $approvedBy): static
    {
        $this->approvedBy = $approvedBy;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * Get total price of all order items
     */
    public function getOrderTotal(): string
    {
        $total = 0;
        foreach ($this->orderItems as $item) {
            $total += floatval($item->getTotalPrice());
        }
        return number_format($total, 2, '.', '');
    }

    /**
     * Calculate and update subtotal and total
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->getOrderTotal();
        $this->total = $this->subtotal; // Add taxes, shipping, etc. here if needed
    }

    /**
     * Check if order can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING || $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if order can be rejected
     */
    public function canBeRejected(): bool
    {
        return $this->status === self::STATUS_PENDING || $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if payment reminder can be sent
     */
    public function canSendPaymentReminder(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_PENDING]);
    }

    /**
     * Validate status transition
     * Returns true if the transition is valid, false otherwise
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $validTransitions = [
            self::STATUS_PENDING => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELLED],
            self::STATUS_APPROVED => [self::STATUS_PAID, self::STATUS_REJECTED, self::STATUS_CANCELLED],
            self::STATUS_REJECTED => [], // Rejected orders cannot transition (need reopen)
            self::STATUS_PAID => [self::STATUS_PROCESSING],
            self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
            self::STATUS_DELIVERED => [], // Delivered is final state
            self::STATUS_CANCELLED => [], // Cancelled is final state
        ];

        if (!isset($validTransitions[$this->status])) {
            return false;
        }

        return in_array($newStatus, $validTransitions[$this->status]);
    }

    /**
     * Get allowed next statuses for current status
     */
    public function getAllowedNextStatuses(): array
    {
        $validTransitions = [
            self::STATUS_PENDING => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELLED],
            self::STATUS_APPROVED => [self::STATUS_PAID, self::STATUS_REJECTED, self::STATUS_CANCELLED],
            self::STATUS_REJECTED => [],
            self::STATUS_PAID => [self::STATUS_PROCESSING],
            self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
            self::STATUS_DELIVERED => [],
            self::STATUS_CANCELLED => [],
        ];

        return $validTransitions[$this->status] ?? [];
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function __toString(): string
    {
        return $this->orderNumber;
    }
}