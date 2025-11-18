<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    private ?ArticleCategory $category = null;

    #[ORM\ManyToOne]
    private ?User $author = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Media $featuredImage = null;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\Column]
    private bool $isFeatured = false;

    #[ORM\Column]
    private int $viewCount = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: ArticleTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getCategory(): ?ArticleCategory
    {
        return $this->category;
    }

    public function setCategory(?ArticleCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getFeaturedImage(): ?Media
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?Media $featuredImage): static
    {
        $this->featuredImage = $featuredImage;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;
        if ($isPublished && !$this->publishedAt) {
            $this->publishedAt = new \DateTimeImmutable();
        }
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

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function incrementViewCount(): static
    {
        $this->viewCount++;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ArticleTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setArticle($this);
        }
        return $this;
    }

    public function getTitle(?string $locale = null): ?string
    {
        if ($locale) {
            foreach ($this->translations as $translation) {
                if ($translation->getLanguage() && $translation->getLanguage()->getCode() === $locale) {
                    return $translation->getTitle();
                }
            }
        }
        return $this->translations->first() ? $this->translations->first()->getTitle() : null;
    }

    public function getExcerpt(?string $locale = null): ?string
    {
        if ($locale) {
            foreach ($this->translations as $translation) {
                if ($translation->getLanguage() && $translation->getLanguage()->getCode() === $locale) {
                    return $translation->getExcerpt();
                }
            }
        }
        return $this->translations->first() ? $this->translations->first()->getExcerpt() : null;
    }

    public function getContent(?string $locale = null): ?string
    {
        if ($locale) {
            foreach ($this->translations as $translation) {
                if ($translation->getLanguage() && $translation->getLanguage()->getCode() === $locale) {
                    return $translation->getContent();
                }
            }
        }
        return $this->translations->first() ? $this->translations->first()->getContent() : null;
    }

    public function getMetaTitle(?string $locale = null): ?string
    {
        if ($locale) {
            foreach ($this->translations as $translation) {
                if ($translation->getLanguage() && $translation->getLanguage()->getCode() === $locale) {
                    return $translation->getMetaTitle() ?: $translation->getTitle();
                }
            }
        }
        $first = $this->translations->first();
        return $first ? ($first->getMetaTitle() ?: $first->getTitle()) : null;
    }

    public function getMetaDescription(?string $locale = null): ?string
    {
        if ($locale) {
            foreach ($this->translations as $translation) {
                if ($translation->getLanguage() && $translation->getLanguage()->getCode() === $locale) {
                    return $translation->getMetaDescription() ?: $translation->getExcerpt();
                }
            }
        }
        $first = $this->translations->first();
        return $first ? ($first->getMetaDescription() ?: $first->getExcerpt()) : null;
    }
}
