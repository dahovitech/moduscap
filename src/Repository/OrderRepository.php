<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Find orders by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find orders pending payment (approved or pending)
     */
    public function findPendingPayment(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('statuses', [Order::STATUS_PENDING, Order::STATUS_APPROVED])
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find orders by client email
     */
    public function findByClientEmail(string $email): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.clientEmail = :email')
            ->setParameter('email', $email)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent orders (last 30 days)
     */
    public function findRecentOrders(int $days = 30): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('o')
            ->andWhere('o.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get orders statistics
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('o');
        
        $totalOrders = $qb
            ->select('COUNT(o.id) as total')
            ->getQuery()
            ->getSingleScalarResult();

        $pendingOrders = $this->findByStatus(Order::STATUS_PENDING);
        $approvedOrders = $this->findByStatus(Order::STATUS_APPROVED);
        $paidOrders = $this->findByStatus(Order::STATUS_PAID);

        $totalRevenue = $qb
            ->select('SUM(o.total) as revenue')
            ->andWhere('o.status = :paid')
            ->setParameter('paid', Order::STATUS_PAID)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => count($pendingOrders),
            'approved_orders' => count($approvedOrders),
            'paid_orders' => count($paidOrders),
            'total_revenue' => floatval($totalRevenue),
        ];
    }

    /**
     * Find orders that need payment reminders
     */
    public function findOrdersNeedingReminders(): array
    {
        // Orders approved or pending for more than 3 days
        $threeDaysAgo = new \DateTime();
        $threeDaysAgo->modify('-3 days');

        return $this->createQueryBuilder('o')
            ->andWhere('o.status IN (:statuses)')
            ->andWhere('o.createdAt <= :date')
            ->setParameter('statuses', [Order::STATUS_PENDING, Order::STATUS_APPROVED])
            ->setParameter('date', $threeDaysAgo)
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search orders by order number or client name/email
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.orderNumber LIKE :query OR o.clientName LIKE :query OR o.clientEmail LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find orders by date range
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.createdAt >= :startDate')
            ->andWhere('o.createdAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}