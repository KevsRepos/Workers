<?php declare(strict_types=1);

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

    /** @return OrderPreset[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('orderPreset')
            ->leftJoin('orderPreset.items', 'item')
            ->addSelect('item')
            ->leftJoin('item.product', 'product')
            ->addSelect('product')
            ->orderBy('orderPreset.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findById(string $id): ?OrderPreset
    {
        return $this->find($id);
    }

    public function save(OrderPreset $orderPreset, bool $flush = false): void
    {
        $this->getEntityManager()->persist($orderPreset);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrderPreset $orderPreset, bool $flush = false): void
    {
        $this->getEntityManager()->remove($orderPreset);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}