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

    /**
     * Выборка заявок по статусу
     */
    public function findByStatus(string $status, int $limit = 100): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Поиск всех заявок, привязанных к конкретному рейсу
     */
    public function findByTripId(int $tripId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.tripId = :tripId')
            ->setParameter('tripId', $tripId)
            ->orderBy('s.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Комбинированная фильтрация (дата + статус + объект)
     */
    public function findByFilters(?\DateTimeInterface $date = null, ?string $status = null, ?string $site = null, int $limit = 100): array
    {
        $qb = $this->createQueryBuilder('s');

        if ($date) {
            $start = \DateTime::createFromInterface($date)->setTime(0, 0, 0);
            $end = \DateTime::createFromInterface($date)->setTime(23, 59, 59);

            $qb->andWhere('s.createdAt BETWEEN :start AND :end')
               ->setParameter('start', $start)
               ->setParameter('end', $end);
        }

        if ($status && $status !== 'ALL') {
            $qb->andWhere('s.status = :status')
               ->setParameter('status', $status);
        }

        if ($site) {
            $qb->andWhere('s.site = :site')
               ->setParameter('site', $site);
        }

        return $qb->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}