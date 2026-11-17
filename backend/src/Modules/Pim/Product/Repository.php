<?php

declare(strict_types=1);

namespace App\Modules\Pim\Product;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Product>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $product): void
    {
        $this->getEntityManager()->persist($product);
    }

    /**
     * @return array{list<Product>, int}
     */
    public function search(?string $query, ProductListFilter $filter, int $offset, int $limit): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('d')
            ->leftJoin('p.deposit', 'd')
            ->orderBy('p.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $this->applyFilter($qb, $filter);

        if ($query !== null && trim($query) !== '') {
            $qb->andWhere('LOWER(p.name) LIKE :q')->setParameter('q', '%' . addcslashes(mb_strtolower(trim($query)), '%_\\') . '%');
        }

        $paginator = new Paginator($qb->getQuery(), fetchJoinCollection: false);

        return [iterator_to_array($paginator->getIterator(), false), \count($paginator)];
    }

    /**
     * @param list<string> $ids
     *
     * @return array<string, Product> indexed by id
     */
    public function findByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, static fn (string $id): bool => Uuid::isValid($id))));
        if ($ids === []) {
            return [];
        }

        $products = $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', array_map(static fn (string $id): string => Uuid::fromString($id)->toBinary(), $ids), ArrayParameterType::BINARY)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($products as $product) {
            $indexed[(string) $product->id] = $product;
        }

        return $indexed;
    }

    private function applyFilter(QueryBuilder $qb, ProductListFilter $filter): void
    {
        if ($filter === ProductListFilter::ARCHIVED) {
            $qb->andWhere('p.archived = true');

            return;
        }

        $qb->andWhere('p.archived = false');
        match ($filter) {
            ProductListFilter::WITH_DEPOSIT => $qb->andWhere('p.deposit IS NOT NULL'),
            ProductListFilter::WITHOUT_DEPOSIT => $qb->andWhere('p.deposit IS NULL'),
            ProductListFilter::SELLABLE => $qb->andWhere('p.sellable = true'),
            ProductListFilter::RENTABLE => $qb->andWhere('p.rentable = true'),
            default => null,
        };
    }
}
