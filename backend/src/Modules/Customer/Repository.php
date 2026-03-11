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

    // ... you can add find/findBy helpers if needed ...

    public function search(string $query): array
    {
        // $customers = $this->em->getRepository(Customer::class)->findBy(
        //     ['firstName' => $query, 'surname' => $query],
        //     ['surname' => 'ASC', 'firstName' => 'ASC'],
        //     20
        // );

        // return $customers;
        return $this->em->createQueryBuilder()
            ->select('c', 'a')
            ->from(Customer::class, 'c')
            ->leftJoin('c.addresses', 'a', 'WITH', 'a.isPrimary = true')
            ->where('LOWER(c.firstName) LIKE LOWER(:query)')
            ->orWhere('LOWER(c.surname) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.surname', 'ASC')
            ->addOrderBy('c.firstName', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();
    }

    public function findById(string $id): ?Customer
    {
        return $this->em->getRepository(Customer::class)->find($id);
    }
}
