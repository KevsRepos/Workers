<?php declare(strict_types=1);

namespace App\Modules\Account;

use App\Modules\Account\Create\AccountRequestData;
use App\Modules\Account\Repository;
use App\Modules\Account\Factory;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Error;
use Exception;

final class Service {
    public function __construct(private Repository $repo, private Factory $account)
    {
    }

    public function save(AccountRequestData $user): Error|Success
    {
        $account = $this->account->create(
            $user->emailAddress,
            $user->password,
            $user->firstName,
            $user->surname
        );

        try {
            $this->repo->save($account, true);
        } catch(UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch(Exception) {
            return new Error("Error", 500);
        }

        return new Success("AccountCreated");
    }
}