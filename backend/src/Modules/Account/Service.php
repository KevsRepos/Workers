<?php declare(strict_types=1);

namespace App\Modules\Account;

use App\Modules\Account\DataCreateAccountRequest;
use App\Modules\Account\Account;
use App\Lib\Success;
use App\Modules\Account\Repository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Error;
use Exception;

final class Service {
    public function __construct(private Repository $repo)
    {
    }

    public function save(DataCreateAccountRequest $user): Error|Success
    {
        $account = new Account();

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
        } catch(Exception $err) {
            return new Error($err->getMessage(), 500);
        }

        return new Success("AccounCreated");
    }
}