<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\DataCreateAccountRequest;
use App\Entity\Account as EntityAccount;
use App\Repository\AccountRepository;
use DateTimeImmutable;

final class AccountService {
    public function __construct(private AccountRepository $repo)
    {
    }

    public function save(DataCreateAccountRequest $user)
    {
        $account = new EntityAccount();

        $account
        ->setEmailAddress($user->emailAddress)
        ->setPassword($user->password)
        ->setFirstName($user->firstName)
        ->setSurname($user->surname)
        ->setCreatedAt(new DateTimeImmutable());

        $this->repo->save($account, true);

        return ['success'];
    }
}