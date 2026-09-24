<?php declare(strict_types=1);

namespace App\Modules\Customer;

use Doctrine\ORM\EntityManagerInterface;

class Repository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function save(Customer $customer, bool $flush = false): Customer
    {
        $this->em->persist($customer);
        if ($flush) {
            $this->em->flush();
        }

        return $customer;
    }

    public function saveDefaultShippingAddress(string $customerId, string $addressId, bool $flush = false): void
    {
        $this->em->createQueryBuilder()
            ->update(Customer::class, 'c')
            ->set('c.defaultShippingAddress', ':addressId')
            ->where('c.id = :customerId')
            ->setParameter('addressId', $addressId, 'uuid')
            ->setParameter('customerId', $customerId, 'uuid')
            ->getQuery()
            ->execute();

        if ($flush) {
            $this->em->flush();
        }
    }

    public function saveDefaultBillingAddress(string $customerId, string $addressId, bool $flush = false): void
    {
        $this->em->createQueryBuilder()
            ->update(Customer::class, 'c')
            ->set('c.defaultBillingAddress', ':addressId')
            ->where('c.id = :customerId')
            ->setParameter('addressId', $addressId, 'uuid')
            ->setParameter('customerId', $customerId, 'uuid')
            ->getQuery()
            ->execute();

        if ($flush) {
            $this->em->flush();
        }
    }

    // ... you can add find/findBy helpers if needed ...

    public function search(string $query): array
    {
        $customers = $this->em->createQueryBuilder()
            ->select('c')
            ->from(Customer::class, 'c')
            ->where(
                $this->em->createQueryBuilder()->expr()->orX(
                    'LOWER(c.firstName) LIKE LOWER(:query)',
                    'LOWER(c.surname) LIKE LOWER(:query)',
                    'LOWER(c.companyName) LIKE LOWER(:query)',
                )
            )
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.surname', 'ASC')
            ->addOrderBy('c.firstName', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();

        return array_map(fn($c) => [
            ...$c,
            'customerName' => $c['company']
                ? $c['companyName']
                : trim(($c['firstName'] ?? '') . ' ' . ($c['surname'] ?? '')),
        ], $customers);
    }

    public function findById(string $id): ?Customer
    {
        return $this->em->getRepository(Customer::class)
            ->createQueryBuilder('c')
            ->select('c', 'a', 'sa', 'ba')
            ->leftJoin('c.addresses', 'a')
            ->leftJoin('c.defaultShippingAddress', 'sa')
            ->leftJoin('c.defaultBillingAddress', 'ba')
            ->where('c.id = :id')
            ->setParameter('id', $id, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
