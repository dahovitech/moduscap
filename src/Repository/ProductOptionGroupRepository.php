<?php

namespace App\Repository;

use App\Entity\ProductOptionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductOptionGroup>
 */
class ProductOptionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductOptionGroup::class);
    }

    public function save(ProductOptionGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductOptionGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProductOptionGroup[] Returns an array of ProductOptionGroup objects
     */
    public function findActiveOptionGroups($locale = null): array
    {
        $qb = $this->createQueryBuilder('g')
            ->leftJoin('g.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('g.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('g.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ProductOptionGroup|null Returns a ProductOptionGroup object by code
     */
    public function findByCode(string $code): ?ProductOptionGroup
    {
        return $this->createQueryBuilder('g')
            ->where('g.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return ProductOptionGroup[] Returns an array of ProductOptionGroup objects for admin
     */
    public function findForAdmin(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.sortOrder', 'ASC')
            ->addOrderBy('g.code', 'ASC')
            ->getQuery()
            ->getResult();
    }
}