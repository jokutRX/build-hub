<?php

namespace App\Repository;

use App\Entity\SupplyRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SupplyRequest>
 */
class SupplyRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupplyRequest::class);
    }

    /**
     * Быстрая выборка заявок за конкретный день
     */
    public function findByDate(\DateTimeInterface $date): array
    {
        return $this->findByDateRange($date);
    }

    /**
     * Оптимизированная выборка заявок за диапазон дат с использованием индекса
     */
    public function findByDateRange(\DateTimeInterface $startDate, ?\DateTimeInterface $endDate = null): array
    {
        $start = \DateTime::createFromInterface($startDate)->setTime(0, 0, 0);
        $end = $endDate 
            ? \DateTime::createFromInterface($endDate)->setTime(23, 59, 59) 
            : \DateTime::createFromInterface($startDate)->setTime(23, 59, 59);

        return $this->createQueryBuilder('s')
            ->andWhere('s.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}