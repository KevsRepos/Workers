<?php declare(strict_types=1);

namespace App\Modules\Customer\Address;

use Doctrine\ORM\EntityManagerInterface;

class Repository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function save(CustomerAddress $address, bool $flush = false): void
    {
        $this->em->persist($address);
        if ($flush) {
            $this->em->flush();
        }
    }

    // Add list/find helpers as needed
}
