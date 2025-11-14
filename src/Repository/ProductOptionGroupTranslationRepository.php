<?php

namespace App\Repository;

use App\Entity\ProductOptionGroupTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductOptionGroupTranslation>
 */
class ProductOptionGroupTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductOptionGroupTranslation::class);
    }

    public function save(ProductOptionGroupTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductOptionGroupTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProductOptionGroupTranslation[] Returns an array of ProductOptionGroupTranslation objects
     */
    public function findByLanguage(string $locale): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.language', 'l')
            ->leftJoin('t.optionGroup', 'g')
            ->where('l.code = :locale')
            ->andWhere('g.isActive = :active')
            ->setParameter('locale', $locale)
            ->setParameter('active', true)
            ->orderBy('g.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}