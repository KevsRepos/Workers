<?php

namespace App\Modules\Pim\ProductUnion;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductUnion>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductUnion::class);
    }

    public function save(ProductUnion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductUnion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findById(string $id): ?ProductUnion
    {
        return $this->find($id);
    }

    public function findProductsByUnion(string $productUnionId): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('pup', 'p')
            ->from(ProductUnionProduct::class, 'pup')
            ->join('pup.product', 'p')
            ->where('pup.productUnion = :id')
            ->setParameter('id', $productUnionId)
            ->getQuery()
            ->getResult();
    }

    public function removeProductsByUnion(string $productUnionId): void
    {
        // $this->getEntityManager()->rem
        $products = $this->getEntityManager()->createQueryBuilder()
            ->select('pup')
            ->from(ProductUnionProduct::class, 'pup')
            ->where('pup.productUnion = :id')
            ->setParameter('id', $productUnionId)
            ->getQuery()
            ->getResult();

        foreach ($products as $product) {
            $this->getEntityManager()->remove($product);
        }
        $this->getEntityManager()->flush();
    }

    public function removeProducts(array $ids): void
    {
        foreach ($ids as $id) {
            $entity = $this->getEntityManager()
                ->getRepository(ProductUnionProduct::class)
                ->find($id);

            if ($entity) {
                $this->getEntityManager()->remove($entity);
            }
        }

        $this->getEntityManager()->flush();
    }

    public function saveProducts(array $products): void
    {
        foreach ($products as $product) {
            $this->getEntityManager()->persist($product);
        }
        $this->getEntityManager()->flush();
    }

    public function listAll(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('pu')
            ->from(ProductUnion::class, 'pu')
            ->orderBy('pu.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }
}
