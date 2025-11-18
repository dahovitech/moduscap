<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findPublished(int $limit = 10, int $offset = 0): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 'at')
            ->leftJoin('a.category', 'c')
            ->leftJoin('a.author', 'u')
            ->addSelect('at', 'c', 'u')
            ->where('a.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('a.publishedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findFeatured(int $limit = 3): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 'at')
            ->leftJoin('a.category', 'c')
            ->addSelect('at', 'c')
            ->where('a.isPublished = :published')
            ->andWhere('a.isFeatured = :featured')
            ->setParameter('published', true)
            ->setParameter('featured', true)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $categoryCode, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 'at')
            ->leftJoin('a.category', 'c')
            ->addSelect('at', 'c')
            ->where('a.isPublished = :published')
            ->andWhere('c.code = :category')
            ->setParameter('published', true)
            ->setParameter('category', $categoryCode)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 'at')
            ->leftJoin('a.category', 'c')
            ->leftJoin('a.author', 'u')
            ->addSelect('at', 'c', 'u')
            ->where('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countPublished(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.isPublished = :published')
            ->setParameter('published', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function search(string $query, string $locale, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 'at')
            ->leftJoin('at.language', 'l')
            ->leftJoin('a.category', 'c')
            ->addSelect('at', 'c')
            ->where('a.isPublished = :published')
            ->andWhere('l.code = :locale')
            ->andWhere('at.title LIKE :query OR at.content LIKE :query')
            ->setParameter('published', true)
            ->setParameter('locale', $locale)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRelated(Article $article, int $limit = 3): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 'at')
            ->addSelect('at')
            ->where('a.isPublished = :published')
            ->andWhere('a.id != :currentId')
            ->setParameter('published', true)
            ->setParameter('currentId', $article->getId())
            ->setMaxResults($limit);

        if ($article->getCategory()) {
            $qb->andWhere('a.category = :category')
               ->setParameter('category', $article->getCategory())
               ->orderBy('a.publishedAt', 'DESC');
        } else {
            $qb->orderBy('RAND()');
        }

        return $qb->getQuery()->getResult();
    }
}
