<?php declare(strict_types=1);

namespace App\Service;

use App\DTO\DataCreateAccountRequest;
use App\Entity\Account as EntityAccount;
use App\Lib\Success;
use App\Repository\AccountRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Error;
use Exception;

final class AccountService {
    public function __construct(private AccountRepository $repo)
    {
    }

    public function save(DataCreateAccountRequest $user): Error|Success
    {
        $account = new EntityAccount();

        $account
        ->setEmailAddress($user->emailAddress)
        ->setPassword($user->password)
        ->setFirstName($user->firstName)
        ->setSurname($user->surname)
        ->setCreatedAt(new DateTimeImmutable());

        try {
            $this->repo->save($account, true);
        } catch(UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch(Exception) {
            return new Error("", 500);
        }

        return new Success("AccounCreated");
    }
}