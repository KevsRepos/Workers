<?php

namespace App\Modules\Pim\Product;

use App\Modules\Pim\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
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

    public function search(string $query): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->where('LOWER(p.name) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Return all products as arrays ordered by name
     *
     * @return array
     */
    public function listAll(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('p', 'd')
            ->from(Product::class, 'p')
            ->leftJoin('p.deposit', 'd')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Return paginated list of products
     *
     * @param int $page 1-based page
     * @param int $limit
     * @return array
     */
    public function listPaginated(int $page, int $limit, ProductListFilter $filter = ProductListFilter::ALL): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p', 'd')
            ->from(Product::class, 'p')
            ->leftJoin('p.deposit', 'd')
            ->orderBy('p.name', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $this->applyFilter($qb, $filter);

        return $qb->getQuery()->getArrayResult();
    }

    public function findById(string $id): ?Product
    {
        return $this->find($id);
    }

    public function countAll(ProductListFilter $filter = ProductListFilter::ALL): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->leftJoin('p.deposit', 'd');

        $this->applyFilter($qb, $filter);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilter(\Doctrine\ORM\QueryBuilder $qb, ProductListFilter $filter): void
    {
        match ($filter) {
            ProductListFilter::WITH_DEPOSIT => $qb->andWhere('p.deposit IS NOT NULL'),
            ProductListFilter::WITHOUT_DEPOSIT => $qb->andWhere('p.deposit IS NULL'),
            ProductListFilter::SELLABLE => $qb->andWhere('p.sellable = true'),
            ProductListFilter::RENTABLE => $qb->andWhere('p.rentable = true'),
            default => null,
        };
    }
}
