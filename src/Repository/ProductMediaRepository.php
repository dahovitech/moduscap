<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductMedia;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ProductMedia>
 */
class ProductMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductMedia::class);
    }

    public function save(ProductMedia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductMedia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProductMedia[] Returns an array of ProductMedia objects for a product
     */
    public function findByProduct($product): array
    {
        return $this->createQueryBuilder('pm')
            ->leftJoin('pm.product', 'p')
            ->leftJoin('pm.media', 'm')
            ->where('p.id = :productId')
            ->setParameter('productId', $product instanceof Product ? $product->getId() : $product)
            ->orderBy('pm.sortOrder', 'ASC')
            ->addOrderBy('pm.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ProductMedia|null Returns the main image for a product
     */
    public function findMainImageByProduct($product): ?ProductMedia
    {
        return $this->createQueryBuilder('pm')
            ->leftJoin('pm.product', 'p')
            ->where('p.id = :productId')
            ->andWhere('pm.isMainImage = :isMainImage')
            ->setParameter('productId', $product instanceof Product ? $product->getId() : $product)
            ->setParameter('isMainImage', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return ProductMedia[] Returns an array of gallery images for a product
     */
    public function findGalleryImagesByProduct($product): array
    {
        return $this->createQueryBuilder('pm')
            ->leftJoin('pm.product', 'p')
            ->leftJoin('pm.media', 'm')
            ->where('p.id = :productId')
            ->andWhere('pm.mediaType = :mediaType')
            ->andWhere('pm.isMainImage = :isMainImage')
            ->setParameter('productId', $product instanceof Product ? $product->getId() : $product)
            ->setParameter('mediaType', 'gallery')
            ->setParameter('isMainImage', false)
            ->orderBy('pm.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ProductMedia[] Returns an array of technical images for a product
     */
    public function findTechnicalImagesByProduct($product): array
    {
        return $this->createQueryBuilder('pm')
            ->leftJoin('pm.product', 'p')
            ->leftJoin('pm.media', 'm')
            ->where('p.id = :productId')
            ->andWhere('pm.mediaType = :mediaType')
            ->setParameter('productId', $product instanceof Product ? $product->getId() : $product)
            ->setParameter('mediaType', 'technical')
            ->orderBy('pm.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}