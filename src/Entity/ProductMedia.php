<?php

namespace App\Entity;

use App\Repository\ProductMediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductMediaRepository::class)
 * @ORM\Table(name="product_media", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_product_media", columns={"product_id", "media_id"})
 * })
 */
class ProductMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['main_image', 'gallery', 'technical', 'lifestyle'])]
    private string $mediaType;

    #[ORM\Column(type: 'boolean')]
    private bool $isMainImage = false;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productMedia')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $media = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    public function isMainImage(): bool
    {
        return $this->isMainImage;
    }

    public function setIsMainImage(bool $isMainImage): static
    {
        $this->isMainImage = $isMainImage;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        $this->media = $media;
        return $this;
    }

    public function __toString(): string
    {
        return $this->mediaType . ' - ' . ($this->media?->getAlt() ?: '');
    }
}