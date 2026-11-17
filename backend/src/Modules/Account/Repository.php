<?php

declare(strict_types=1);

namespace App\Modules\Account;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function findByEmail(string $email): ?Account
    {
        return $this->findOneBy(['emailAddress' => Account::normalizeEmail($email)]);
    }

    /**
     * @return list<Account>
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.surname', 'ASC')
            ->addOrderBy('a.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveAdmins(): int
    {
        $count = 0;
        foreach ($this->findBy(['active' => true]) as $account) {
            if ($account->isAdmin()) {
                ++$count;
            }
        }

        return $count;
    }

    public function add(Account $account): void
    {
        $this->getEntityManager()->persist($account);
    }
}
