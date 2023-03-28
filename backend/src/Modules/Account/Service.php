<?php declare(strict_types=1);

namespace App\Modules\Account;

use Error;
use Exception;
use App\Modules\Account\DTO\CreateAccountRequest;
use App\Modules\Account\Repository;
use App\Modules\Account\Factory;
use App\Lib\Success;
use App\Modules\Account\DTO\UpdateAccountRequest;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Lib\JWTExtractor;

final class Service {
    public function __construct(
        private Repository $repo, 
        private Factory $account, 
        private UserPasswordHasherInterface $hasher,
        private JWTExtractor $jwtExtractor
    ){}

    public function save(CreateAccountRequest $data): Error|Success
    {
        $account = $this->account->create(
            $data->emailAddress,
            $data->firstName,
            $data->surname
        );

        $hashedPassword = $this->hasher->hashPassword(
            $account,
            $data->password
        );

        $account->setPassword($hashedPassword);

        try {
            $this->repo->save($account, true);
        } catch(UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch(Exception) {
            return new Error("Error", 500);
        }

        return new Success("AccountCreated");
    }

    public function update(UpdateAccountRequest $data): Error|Success
    {
        $account = $this->repo->findOneBy(
            ['accountId' => $this->jwtExtractor->getUserId()]
        );

        !empty($data->emailAddress) && $account->setEmailAddress($data->emailAddress);
        !empty($data->firstName) && $account->setFirstName($data->firstName);
        !empty($data->surname) && $account->setSurname($data->surname);

        $account->setUpdatedAt();

        if(!empty($data->password)) {
            $hashedPassword = $this->hasher->hashPassword(
                $account,
                $data->password
            );
    
            $account->setPassword($hashedPassword);
        }

        try {
            $this->repo->flush();
        } catch(UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch(Exception) {
            return new Error("Error", 500);
        }

        return new Success("AccountUpdated");
    }
}