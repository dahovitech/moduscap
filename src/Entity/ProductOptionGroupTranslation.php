<?php

namespace App\Entity;

use App\Repository\ProductOptionGroupTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductOptionGroupTranslationRepository::class)
 * @ORM\Table(name="product_option_group_translations", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_option_group_translation", columns={"product_option_group_id", "language_id"})
 * })
 */
class ProductOptionGroupTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: ProductOptionGroup::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ProductOptionGroup $optionGroup = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}