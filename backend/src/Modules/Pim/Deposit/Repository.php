<?php

namespace App\Modules\Pim\Deposit;

use App\Modules\Pim\Deposit\Deposit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deposit>
 *
 * @method Deposit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deposit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deposit[]    findAll()
 * @method Deposit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deposit::class);
    }

    public function save(Deposit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Deposit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
