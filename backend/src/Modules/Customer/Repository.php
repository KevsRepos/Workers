<?php

declare(strict_types=1);

namespace App\Modules\Customer;

use App\Modules\Customer\Address\CustomerAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function add(Customer $customer): void
    {
        $this->getEntityManager()->persist($customer);
    }

    public function findWithAddresses(string $id): ?Customer
    {
        return $this->createQueryBuilder('c')
            ->addSelect('a')
            ->leftJoin('c.addresses', 'a')
            ->where('c.id = :id')
            ->setParameter('id', $id, 'app_uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAddress(string $id): ?CustomerAddress
    {
        return $this->getEntityManager()->find(CustomerAddress::class, $id);
    }

    /**
     * @return array{list<Customer>, int}
     */
    public function search(?string $query, int $offset, int $limit): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.anonymizedAt IS NULL')
            ->orderBy('c.companyName', 'ASC')
            ->addOrderBy('c.surname', 'ASC')
            ->addOrderBy('c.firstName', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($query !== null && $query !== '') {
            $terms = preg_split('/\s+/', mb_strtolower($query)) ?: [];
            foreach (array_values(array_filter($terms)) as $i => $term) {
                $qb->andWhere(\sprintf(
                    '(LOWER(c.firstName) LIKE :t%1$d OR LOWER(c.surname) LIKE :t%1$d OR LOWER(c.companyName) LIKE :t%1$d OR LOWER(c.email) LIKE :t%1$d OR c.phone LIKE :t%1$d)',
                    $i,
                ))->setParameter('t' . $i, '%' . addcslashes($term, '%_\\') . '%');
            }
        }

        $paginator = new Paginator($qb->getQuery(), fetchJoinCollection: false);

        return [iterator_to_array($paginator->getIterator(), false), \count($paginator)];
    }

    public function hasDeliveryNotes(Customer $customer): bool
    {
        return (int) $this->getEntityManager()->createQuery(
            'SELECT COUNT(d.id) FROM App\Modules\Pim\DeliveryNote\DeliveryNote d WHERE d.customer = :customer',
        )->setParameter('customer', $customer->id, 'app_uuid')->getSingleScalarResult() > 0;
    }
}
