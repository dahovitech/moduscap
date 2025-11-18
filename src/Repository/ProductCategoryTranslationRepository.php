<?php

namespace App\Repository;

use App\Entity\ProductCategoryTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductCategoryTranslation>
 */
class ProductCategoryTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCategoryTranslation::class);
    }

    public function save(ProductCategoryTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductCategoryTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProductCategoryTranslation[] Returns an array of ProductCategoryTranslation objects
     */
    public function findByLanguage(string $locale): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.language', 'l')
            ->leftJoin('t.productCategory', 'c')
            ->where('l.code = :locale')
            ->andWhere('c.isActive = :active')
            ->setParameter('locale', $locale)
            ->setParameter('active', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}