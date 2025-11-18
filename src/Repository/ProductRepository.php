<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findActiveProducts($locale = null, $category = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        if ($category) {
            if ($category instanceof ProductCategory) {
                $qb->andWhere('p.category = :category')
                    ->setParameter('category', $category);
            } else {
                $qb->andWhere('c.id = :categoryId')
                    ->setParameter('categoryId', $category);
            }
        }

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        $qb->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findFeaturedProducts($locale = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('p.isActive = :active')
            ->andWhere('p.isFeatured = :featured')
            ->setParameter('active', true)
            ->setParameter('featured', true);

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        $qb->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findCustomizableProducts($locale = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('p.isActive = :active')
            ->andWhere('p.isCustomizable = :customizable')
            ->setParameter('active', true)
            ->setParameter('customizable', true);

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        $qb->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Product|null Returns a Product object by code
     */
    public function findByCode(string $code): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('p.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Product[] Returns an array of Product objects for admin
     */
    public function findForAdmin(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[] Returns an array of Product objects by category
     */
    public function findByCategory(ProductCategory $category, $locale = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->leftJoin('p.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('c.id = :categoryId')
            ->andWhere('p.isActive = :active')
            ->setParameter('categoryId', $category->getId())
            ->setParameter('active', true);

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        $qb->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Product[] Returns an array of Product objects by price range
     */
    public function findByPriceRange($minPrice, $maxPrice, $locale = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('p.isActive = :active')
            ->andWhere('p.basePrice >= :minPrice')
            ->andWhere('p.basePrice <= :maxPrice')
            ->setParameter('active', true)
            ->setParameter('minPrice', $minPrice)
            ->setParameter('maxPrice', $maxPrice);

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        $qb->orderBy('p.basePrice', 'ASC')
            ->addOrderBy('p.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }
}