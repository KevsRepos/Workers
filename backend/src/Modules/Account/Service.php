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
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class Service {
    public function __construct(
        private Repository $repo, 
        private Factory $account, 
        private UserPasswordHasherInterface $hasher,
        private JWTExtractor $jwtExtractor,
		private JWTTokenManagerInterface $jwtManager,
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

        $account->password = $hashedPassword;

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

        !empty($data->emailAddress) && $account->emailAddress = $data->emailAddress;
        !empty($data->firstName) && $account->firstName = $data->firstName;
        !empty($data->surname) && $account->surname = $data->surname;

        $account->updatedAt = new DateTimeImmutable();

        if(!empty($data->password)) {
            $hashedPassword = $this->hasher->hashPassword(
                $account,
                $data->password
            );
    
            $account->password = $hashedPassword;
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

	public function login(string $emailAddress, string $password): ?string
	{
		$account = $this->repo->findOneBy(
			['emailAddress' => $emailAddress]
		);

		if (!$account) {
			return null;
		}

		if (!$this->hasher->isPasswordValid($account, $password)) {
			return null;
		}

		return $this->jwtManager->create($account);
	}

    public function getAccountByAuthId(): Account
    {
        $account = $this->repo->findOneBy(
            ['accountId' => $this->jwtExtractor->getUserId()]
        );

        return $account;
    }
}