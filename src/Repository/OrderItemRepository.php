<?php

namespace App\Repository;

use App\Entity\OrderItem;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 *
 * @method OrderItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderItem[]    findAll()
 * @method OrderItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * Find order items by order
     */
    public function findByOrder(Order $order): array
    {
        return $this->createQueryBuilder('oi')
            ->join('oi.product', 'p')
            ->addSelect('p')
            ->andWhere('oi.order = :order')
            ->setParameter('order', $order)
            ->orderBy('oi.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find order items by product
     */
    public function findByProduct($productId): array
    {
        return $this->createQueryBuilder('oi')
            ->andWhere('oi.product = :product')
            ->setParameter('product', $productId)
            ->orderBy('oi.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total quantity sold for a product
     */
    public function getTotalSoldForProduct($productId): int
    {
        $result = $this->createQueryBuilder('oi')
            ->select('SUM(oi.quantity) as total')
            ->join('oi.order', 'o')
            ->andWhere('oi.product = :product')
            ->andWhere('o.status IN (:paidStatuses)')
            ->setParameter('product', $productId)
            ->setParameter('paidStatuses', [Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])
            ->getQuery()
            ->getSingleScalarResult();

        return intval($result ?? 0);
    }

    /**
     * Get most popular products based on sales
     */
    public function getMostPopularProducts(int $limit = 10): array
    {
        return $this->createQueryBuilder('oi')
            ->select('p.id, p.code, SUM(oi.quantity) as total_sold, AVG(oi.unitPrice) as avg_price')
            ->join('oi.product', 'p')
            ->join('oi.order', 'o')
            ->andWhere('o.status IN (:paidStatuses)')
            ->setParameter('paidStatuses', [Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])
            ->groupBy('p.id, p.code')
            ->orderBy('total_sold', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get revenue by product
     */
    public function getRevenueByProduct(): array
    {
        return $this->createQueryBuilder('oi')
            ->select('p.code, p.id, SUM(oi.totalPrice) as revenue, SUM(oi.quantity) as quantity_sold')
            ->join('oi.product', 'p')
            ->join('oi.order', 'o')
            ->andWhere('o.status IN (:paidStatuses)')
            ->setParameter('paidStatuses', [Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])
            ->groupBy('p.id, p.code')
            ->orderBy('revenue', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find order items with specific options
     */
    public function findItemsWithOptions(string $optionCode): array
    {
        return $this->createQueryBuilder('oi')
            ->andWhere('JSON_CONTAINS(oi.selectedOptions, :optionCode) = 1')
            ->setParameter('optionCode', '"' . $optionCode . '"')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get average order value by product
     */
    public function getAverageOrderValueForProduct($productId): float
    {
        $result = $this->createQueryBuilder('oi')
            ->select('AVG(oi.totalPrice) as avg_value')
            ->join('oi.order', 'o')
            ->andWhere('oi.product = :product')
            ->andWhere('o.status IN (:paidStatuses)')
            ->setParameter('product', $productId)
            ->setParameter('paidStatuses', [Order::STATUS_PAID, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED])
            ->getQuery()
            ->getSingleScalarResult();

        return floatval($result ?? 0);
    }
}