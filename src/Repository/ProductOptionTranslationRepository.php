<?php

namespace App\Repository;

use App\Entity\ProductOptionTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductOptionTranslation>
 */
class ProductOptionTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductOptionTranslation::class);
    }

    public function save(ProductOptionTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductOptionTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProductOptionTranslation[] Returns an array of ProductOptionTranslation objects
     */
    public function findByLanguage(string $locale): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.language', 'l')
            ->leftJoin('t.option', 'o')
            ->where('l.code = :locale')
            ->andWhere('o.isActive = :active')
            ->setParameter('locale', $locale)
            ->setParameter('active', true)
            ->orderBy('o.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}