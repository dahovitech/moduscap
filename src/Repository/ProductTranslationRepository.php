<?php

namespace App\Repository;

use App\Entity\ProductTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductTranslation>
 */
class ProductTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductTranslation::class);
    }

    public function save(ProductTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProductTranslation[] Returns an array of ProductTranslation objects
     */
    public function findByLanguage(string $locale): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.language', 'l')
            ->leftJoin('t.product', 'p')
            ->where('l.code = :locale')
            ->andWhere('p.isActive = :active')
            ->setParameter('locale', $locale)
            ->setParameter('active', true)
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ProductTranslation|null Returns a ProductTranslation object by product and language
     */
    public function findByProductAndLanguage($productId, string $locale): ?ProductTranslation
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.language', 'l')
            ->leftJoin('t.product', 'p')
            ->where('p.id = :productId')
            ->andWhere('l.code = :locale')
            ->setParameter('productId', $productId)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getOneOrNullResult();
    }
}