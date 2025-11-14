<?php

namespace App\Repository;

use App\Entity\ProductCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductCategory>
 */
class ProductCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCategory::class);
    }

    public function save(ProductCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProductCategory[] Returns an array of ProductCategory objects
     */
    public function findActiveCategories($locale = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ProductCategory[] Returns an array of ProductCategory objects
     */
    public function findFeaturedCategories($locale = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('c.isActive = :active')
            ->andWhere('c.isFeatured = :featured')
            ->setParameter('active', true)
            ->setParameter('featured', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ProductCategory|null Returns a ProductCategory object by code
     */
    public function findByCode(string $code): ?ProductCategory
    {
        return $this->createQueryBuilder('c')
            ->where('c.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return ProductCategory[] Returns an array of ProductCategory objects for admin
     */
    public function findForAdmin(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.code', 'ASC')
            ->getQuery()
            ->getResult();
    }
}