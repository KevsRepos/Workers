<?php

declare(strict_types=1);

namespace App\Modules\TimeTracking\Reminder;

use App\Modules\Account\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReminderSetting>
 */
class ReminderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReminderSetting::class);
    }

    public function findForAccount(Account $account): ?ReminderSetting
    {
        return $this->findOneBy(['account' => $account]);
    }

    /**
     * @return list<ReminderSetting>
     */
    public function findEnabled(): array
    {
        return $this->createQueryBuilder('r')
            ->addSelect('a')
            ->join('r.account', 'a')
            ->where('r.enabled = true AND a.active = true')
            ->getQuery()
            ->getResult();
    }

    public function add(ReminderSetting $setting): void
    {
        $this->getEntityManager()->persist($setting);
    }
}
