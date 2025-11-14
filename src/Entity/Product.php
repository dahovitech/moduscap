<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    #[Gedmo\Slug(fields: ['code'])]
    private string $code;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $basePrice = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $surface = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $dimensions = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rooms = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $height = null;

    // Materials, equipment, specifications, advantages fields moved to ProductTranslation entity for multilingual support

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $technicalSpecs = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $assemblyTime = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $energyClass = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $warrantyStructure = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $warrantyEquipment = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'boolean')]
    private bool $isFeatured = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isCustomizable = false;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: ProductCategory::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductCategory $category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductMedia::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $productMedia;

    #[ORM\ManyToMany(targetEntity: ProductOption::class, inversedBy: 'products')]
    #[ORM\JoinTable(name: 'product_available_options')]
    private Collection $availableOptions;

    #[ORM\ManyToMany(targetEntity: ProductOption::class)]
    #[ORM\JoinTable(name: 'product_selected_options')]
    private Collection $options;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->productMedia = new ArrayCollection();
        $this->availableOptions = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getBasePrice(): ?string
    {
        return $this->basePrice;
    }

    public function setBasePrice(?string $basePrice): static
    {
        $this->basePrice = $basePrice;
        return $this;
    }

    public function getSurface(): ?string
    {
        return $this->surface;
    }

    public function setSurface(?string $surface): static
    {
        $this->surface = $surface;
        return $this;
    }

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }

    public function setDimensions(?string $dimensions): static
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(?int $rooms): static
    {
        $this->rooms = $rooms;
        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;
        return $this;
    }

    // getMaterials, setMaterials, getEquipment, setEquipment, getSpecifications, setSpecifications, getAdvantages, setAdvantages
    // methods removed - fields moved to ProductTranslation entity for multilingual support

    public function getTechnicalSpecs(): ?string
    {
        return $this->technicalSpecs;
    }

    public function setTechnicalSpecs(?string $technicalSpecs): static
    {
        $this->technicalSpecs = $technicalSpecs;
        return $this;
    }

    public function getAssemblyTime(): ?int
    {
        return $this->assemblyTime;
    }

    public function setAssemblyTime(?int $assemblyTime): static
    {
        $this->assemblyTime = $assemblyTime;
        return $this;
    }

    public function getEnergyClass(): ?string
    {
        return $this->energyClass;
    }

    public function setEnergyClass(?string $energyClass): static
    {
        $this->energyClass = $energyClass;
        return $this;
    }

    public function getWarrantyStructure(): ?int
    {
        return $this->warrantyStructure;
    }

    public function setWarrantyStructure(?int $warrantyStructure): static
    {
        $this->warrantyStructure = $warrantyStructure;
        return $this;
    }

    public function getWarrantyEquipment(): ?int
    {
        return $this->warrantyEquipment;
    }

    public function setWarrantyEquipment(?int $warrantyEquipment): static
    {
        $this->warrantyEquipment = $warrantyEquipment;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    public function isCustomizable(): bool
    {
        return $this->isCustomizable;
    }

    public function setIsCustomizable(bool $isCustomizable): static
    {
        $this->isCustomizable = $isCustomizable;
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

    public function getCategory(): ?ProductCategory
    {
        return $this->category;
    }

    public function setCategory(?ProductCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, ProductTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProductTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setProduct($this);
        }

        return $this;
    }

    public function removeTranslation(ProductTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getProduct() === $this) {
                $translation->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductMedia>
     */
    public function getProductMedia(): Collection
    {
        return $this->productMedia;
    }

    public function addProductMedia(ProductMedia $productMedia): static
    {
        if (!$this->productMedia->contains($productMedia)) {
            $this->productMedia->add($productMedia);
            $productMedia->setProduct($this);
        }

        return $this;
    }

    public function removeProductMedia(ProductMedia $productMedia): static
    {
        if ($this->productMedia->removeElement($productMedia)) {
            // set the owning side to null (unless already changed)
            if ($productMedia->getProduct() === $this) {
                $productMedia->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductOption>
     */
    public function getAvailableOptions(): Collection
    {
        return $this->availableOptions;
    }

    public function addAvailableOption(ProductOption $availableOption): static
    {
        if (!$this->availableOptions->contains($availableOption)) {
            $this->availableOptions->add($availableOption);
        }

        return $this;
    }

    public function removeAvailableOption(ProductOption $availableOption): static
    {
        $this->availableOptions->removeElement($availableOption);

        return $this;
    }

    /**
     * @return Collection<int, ProductOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(ProductOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
        }

        return $this;
    }

    public function removeOption(ProductOption $option): static
    {
        $this->options->removeElement($option);

        return $this;
    }

    /**
     * Get translation by language code
     */
    public function getTranslation(?string $locale = null): ?ProductTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLanguage()->getCode() === $locale) {
                return $translation;
            }
        }
        return null;
    }

    /**
     * Get name in specified locale or default
     */
    public function getName(?string $locale = null): string
    {
        $translation = $this->getTranslation($locale);
        return $translation?->getName() ?: '';
    }

    /**
     * Get description in specified locale or default
     */
    public function getDescription(?string $locale = null): string
    {
        $translation = $this->getTranslation($locale);
        return $translation?->getDescription() ?: '';
    }

    /**
     * Get concept in specified locale or default
     */
    public function getConcept(?string $locale = null): string
    {
        $translation = $this->getTranslation($locale);
        return $translation?->getConcept() ?: '';
    }

    /**
     * Get main image for the product
     */
    public function getMainImage(): ?Media
    {
        foreach ($this->productMedia as $productMedia) {
            if ($productMedia->isMainImage()) {
                return $productMedia->getMedia();
            }
        }
        return null;
    }

    /**
     * Get gallery images for the product
     */
    public function getGalleryImages(): array
    {
        $images = [];
        foreach ($this->productMedia as $productMedia) {
            if ($productMedia->getMediaType() === 'gallery' && !$productMedia->isMainImage()) {
                $images[] = $productMedia->getMedia();
            }
        }
        return $images;
    }

    /**
     * Get technical images for the product
     */
    public function getTechnicalImages(): array
    {
        $images = [];
        foreach ($this->productMedia as $productMedia) {
            if ($productMedia->getMediaType() === 'technical') {
                $images[] = $productMedia->getMedia();
            }
        }
        return $images;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName() ?: $this->code;
    }
}