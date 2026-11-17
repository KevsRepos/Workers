<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking;

use App\Modules\Account\Account;
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

    public function add(MonthlyTimeSheet $sheet): void
    {
        $this->getEntityManager()->persist($sheet);
    }

    public function findForMonth(Account $account, int $year, int $month): ?MonthlyTimeSheet
    {
        return $this->createQueryBuilder('t')
            ->addSelect('e')
            ->leftJoin('t.entries', 'e')
            ->where('t.account = :account AND t.year = :year AND t.month = :month')
            ->setParameter('account', $account->id, 'app_uuid')
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<MonthlyTimeSheet>
     */
    public function findForYear(Account $account, int $year): array
    {
        return $this->createQueryBuilder('t')
            ->addSelect('e')
            ->leftJoin('t.entries', 'e')
            ->where('t.account = :account AND t.year = :year')
            ->setParameter('account', $account->id, 'app_uuid')
            ->setParameter('year', $year)
            ->orderBy('t.month', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<int>
     */
    public function findYears(Account $account): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('DISTINCT t.year')
            ->where('t.account = :account')
            ->setParameter('account', $account->id, 'app_uuid')
            ->orderBy('t.year', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        return array_map('intval', $rows);
    }

    public function findEntry(string $id): ?MonthlyTimeSheetEntry
    {
        return $this->getEntityManager()->find(MonthlyTimeSheetEntry::class, $id);
    }

    public function removeEntry(MonthlyTimeSheetEntry $entry): void
    {
        $entry->timeSheet->entries->removeElement($entry);
        $this->getEntityManager()->remove($entry);
    }
}
