<?php

namespace App\Entity;

use App\Repository\ProductOptionGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductOptionGroupRepository::class)]
#[ORM\Table(name: 'product_option_groups')]
class ProductOptionGroup
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

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['select', 'radio', 'checkbox', 'multiselect'])]
    private string $inputType = 'select';

    #[ORM\Column(type: 'boolean')]
    private bool $isRequired = false;

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

    #[ORM\OneToMany(mappedBy: 'optionGroup', targetEntity: ProductOptionGroupTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    #[ORM\OneToMany(mappedBy: 'optionGroup', targetEntity: ProductOption::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $options;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
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

    public function getInputType(): string
    {
        return $this->inputType;
    }

    public function setInputType(string $inputType): static
    {
        $this->inputType = $inputType;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;
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

    /**
     * @return Collection<int, ProductOptionGroupTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProductOptionGroupTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setOptionGroup($this);
        }

        return $this;
    }

    public function removeTranslation(ProductOptionGroupTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getOptionGroup() === $this) {
                $translation->setOptionGroup(null);
            }
        }

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
            $option->setOptionGroup($this);
        }

        return $this;
    }

    public function removeOption(ProductOption $option): static
    {
        if ($this->options->removeElement($option)) {
            // set the owning side to null (unless already changed)
            if ($option->getOptionGroup() === $this) {
                $option->setOptionGroup(null);
            }
        }

        return $this;
    }

    /**
     * Get translation by language code
     */
    public function getTranslation(?string $locale = null): ?ProductOptionGroupTranslation
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