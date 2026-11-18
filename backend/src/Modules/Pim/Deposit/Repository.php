<?php

declare(strict_types=1);

namespace App\Modules\Pim\Deposit;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deposit>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deposit::class);
    }

    /**
     * @return list<Deposit>
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['singleAmount' => 'ASC', 'crateAmount' => 'ASC']);
    }

    public function add(Deposit $deposit): void
    {
        $this->getEntityManager()->persist($deposit);
    }
}
