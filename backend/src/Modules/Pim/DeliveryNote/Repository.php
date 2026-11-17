<?php

declare(strict_types=1);

namespace App\Modules\Pim\DeliveryNote;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeliveryNote>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryNote::class);
    }

    public function add(DeliveryNote $note): void
    {
        $this->getEntityManager()->persist($note);
    }

    public function findDetailed(string $id): ?DeliveryNote
    {
        return $this->createQueryBuilder('d')
            ->addSelect('c', 'l', 'p', 'r', 'sa', 'ba')
            ->join('d.customer', 'c')
            ->leftJoin('d.lines', 'l')
            ->leftJoin('l.product', 'p')
            ->leftJoin('l.returnNoteEntry', 'r')
            ->leftJoin('d.shippingAddress', 'sa')
            ->leftJoin('d.billingAddress', 'ba')
            ->where('d.id = :id')
            ->setParameter('id', $id, 'app_uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array{list<DeliveryNote>, int}
     */
    public function search(?DeliveryNoteStatus $status, ?string $query, ?\DateTimeImmutable $from, ?\DateTimeImmutable $to, int $offset, int $limit, ?string $customerId = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->addSelect('c', 'sa')
            ->join('d.customer', 'c')
            ->leftJoin('d.shippingAddress', 'sa')
            ->orderBy('d.deliveryDate', $status === DeliveryNoteStatus::OPEN ? 'ASC' : 'DESC')
            ->addOrderBy('d.number', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($status !== null) {
            $qb->andWhere('d.status = :status')->setParameter('status', $status);
        }
        if ($customerId !== null) {
            $qb->andWhere('d.customer = :customer')->setParameter('customer', $customerId, 'app_uuid');
        }
        if ($from !== null) {
            $qb->andWhere('d.deliveryDate >= :from')->setParameter('from', $from, 'date_immutable');
        }
        if ($to !== null) {
            $qb->andWhere('d.deliveryDate <= :to')->setParameter('to', $to, 'date_immutable');
        }
        if ($query !== null && trim($query) !== '') {
            $qb->andWhere('(LOWER(d.number) LIKE :q OR LOWER(c.companyName) LIKE :q OR LOWER(c.surname) LIKE :q OR LOWER(c.firstName) LIKE :q OR LOWER(d.shortDescription) LIKE :q OR LOWER(d.assignment) LIKE :q)')
                ->setParameter('q', '%' . addcslashes(mb_strtolower(trim($query)), '%_\\') . '%');
        }

        $paginator = new Paginator($qb->getQuery(), fetchJoinCollection: false);

        return [iterator_to_array($paginator->getIterator(), false), \count($paginator)];
    }

    /**
     * @return array<int, int> number of notes per status value
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('d')
            ->select('d.status AS status', 'COUNT(d.id) AS total')
            ->groupBy('d.status')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach (DeliveryNoteStatus::cases() as $case) {
            $counts[$case->value] = 0;
        }
        foreach ($rows as $row) {
            $status = $row['status'] instanceof DeliveryNoteStatus ? $row['status']->value : (int) $row['status'];
            $counts[$status] = (int) $row['total'];
        }

        return $counts;
    }
}
