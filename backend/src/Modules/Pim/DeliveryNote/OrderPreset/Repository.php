<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote\OrderPreset;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderPreset>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderPreset::class);
    }

    /**
     * @return list<OrderPreset>
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('i', 'p')
            ->leftJoin('o.items', 'i')
            ->leftJoin('i.product', 'p')
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function add(OrderPreset $preset): void
    {
        $this->getEntityManager()->persist($preset);
    }

    public function remove(OrderPreset $preset): void
    {
        $this->getEntityManager()->remove($preset);
    }
}
