<?php

namespace App\Repository;

use App\Entity\ProductOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductOption>
 */
class ProductOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductOption::class);
    }

    public function save(ProductOption $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductOption $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProductOption[] Returns an array of ProductOption objects
     */
    public function findActiveOptionsByGroup($optionGroup, $locale = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.optionGroup', 'g')
            ->leftJoin('o.translations', 't')
            ->leftJoin('t.language', 'l')
            ->where('g.id = :groupId')
            ->andWhere('o.isActive = :active')
            ->setParameter('groupId', $optionGroup instanceof ProductOptionGroup ? $optionGroup->getId() : $optionGroup)
            ->setParameter('active', true)
            ->orderBy('o.sortOrder', 'ASC')
            ->addOrderBy('t.name', 'ASC');

        if ($locale) {
            $qb->andWhere('l.code = :locale')
                ->setParameter('locale', $locale);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ProductOption|null Returns a ProductOption object by code
     */
    public function findByCode(string $code): ?ProductOption
    {
        return $this->createQueryBuilder('o')
            ->where('o.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return ProductOption[] Returns an array of ProductOption objects for admin
     */
    public function findForAdmin(): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.optionGroup', 'g')
            ->orderBy('g.sortOrder', 'ASC')
            ->addOrderBy('o.sortOrder', 'ASC')
            ->addOrderBy('o.code', 'ASC')
            ->getQuery()
            ->getResult();
    }
}