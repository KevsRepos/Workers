<?php

declare(strict_types=1);

namespace App\Modules\Pim\ProductUnion;

use App\Modules\Pim\Product\Product;
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

    public function add(ProductUnion $union): void
    {
        $this->getEntityManager()->persist($union);
    }

    public function remove(ProductUnion $union): void
    {
        $this->getEntityManager()->remove($union);
    }

    /**
     * @return list<ProductUnion>
     */
    public function findAllWithProducts(): array
    {
        return $this->createQueryBuilder('u')
            ->addSelect('m', 'p')
            ->leftJoin('u.products', 'm')
            ->leftJoin('m.product', 'p')
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<Product> $products
     *
     * @return array<string, ProductUnion> union indexed by product id
     */
    public function findUnionsForProducts(array $products): array
    {
        if ($products === []) {
            return [];
        }

        /** @var list<ProductUnionProduct> $memberships */
        $memberships = $this->getEntityManager()->createQueryBuilder()
            ->select('m', 'u')
            ->from(ProductUnionProduct::class, 'm')
            ->join('m.productUnion', 'u')
            ->where('m.product IN (:products)')
            ->setParameter('products', $products)
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($memberships as $membership) {
            $map[(string) $membership->product->id] = $membership->productUnion;
        }

        return $map;
    }
}
