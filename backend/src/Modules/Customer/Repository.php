<?php declare(strict_types=1);

namespace App\Modules\Customer;

use Doctrine\ORM\EntityManagerInterface;

class Repository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function save(Customer $customer, bool $flush = false): void
    {
        $this->em->persist($customer);
        if ($flush) {
            $this->em->flush();
        }
    }

    // ... you can add find/findBy helpers if needed ...
}
