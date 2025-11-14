<?php

namespace App\Entity;

use App\Repository\ProductOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductOptionRepository::class)]
#[ORM\Table(name: 'product_options')]
class ProductOption
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

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: ProductOptionGroup::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductOptionGroup $optionGroup = null;

    #[ORM\OneToMany(mappedBy: 'option', targetEntity: ProductOptionTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'options')]
    private Collection $products;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->products = new ArrayCollection();
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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getOptionGroup(): ?ProductOptionGroup
    {
        return $this->optionGroup;
    }

    public function setOptionGroup(?ProductOptionGroup $optionGroup): static
    {
        $this->optionGroup = $optionGroup;
        return $this;
    }

    /**
     * @return Collection<int, ProductOptionTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProductOptionTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setOption($this);
        }

        return $this;
    }

    public function removeTranslation(ProductOptionTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getOption() === $this) {
                $translation->setOption(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addOption($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            $product->removeOption($this);
        }

        return $this;
    }

    /**
     * Get translation by language code
     */
    public function getTranslation(?string $locale = null): ?ProductOptionTranslation
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