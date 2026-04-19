<?php declare(strict_types=1);

namespace App\Modules\TimeTracking;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MonthlyTimeSheet>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonthlyTimeSheet::class);
    }

    public function save(MonthlyTimeSheet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MonthlyTimeSheet $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findById(string $id): ?MonthlyTimeSheet
    {
        return $this->find($id);
    }

    public function findYearsByAccount(string $accountId): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT t.year')
            ->from(MonthlyTimeSheet::class, 't')
            ->where('t.account = :accountId')
            ->setParameter('accountId', $accountId, 'uuid')
            ->orderBy('t.year', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findByAccountAndYear(string $accountId, int $year): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from(MonthlyTimeSheet::class, 't')
            ->where('t.account = :accountId')
            ->andWhere('t.year = :year')
            ->setParameter('accountId', $accountId, 'uuid')
            ->setParameter('year', $year)
            ->orderBy('t.month', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findByAccountMonthYear(string $accountId, int $month, int $year): ?MonthlyTimeSheet
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('t', 'e')
            ->from(MonthlyTimeSheet::class, 't')
            ->leftJoin('t.MonthlyTimeSheetEntries', 'e')
            ->where('t.account = :accountId')
            ->andWhere('t.month = :month')
            ->andWhere('t.year = :year')
            ->setParameter('accountId', $accountId, 'uuid')
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function saveEntry(MonthlyTimeSheetEntry $entry, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entry);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function removeEntry(MonthlyTimeSheetEntry $entry, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entry);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findEntryById(string $id): ?MonthlyTimeSheetEntry
    {
        return $this->getEntityManager()->getRepository(MonthlyTimeSheetEntry::class)->find($id);
    }

    /**
     * @return TimeTrackingNotification[]
     */
    public function findAllNotifications(): array
    {
        return $this->getEntityManager()->getRepository(TimeTrackingNotification::class)->findAll();
    }
}
