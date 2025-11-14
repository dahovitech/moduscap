<?php

namespace App\Repository;

use App\Entity\Media;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Rechercher des médias par texte
     * 
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @return Media[]
     */
    public function findBySearch(string $search, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.alt LIKE :search OR m.fileName LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter les médias correspondant à une recherche
     * 
     * @param string $search
     * @return int
     */
    public function countBySearch(string $search): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.alt LIKE :search OR m.fileName LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
