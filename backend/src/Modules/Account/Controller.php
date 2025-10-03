<?php declare(strict_types=1);

namespace App\Modules\Account;

use App\Modules\Account\DTO\AccountDataResponseDto;
use App\Modules\Account\DTO\UpdateAccountRequestDto;
use App\Modules\Account\DTO\CreateAccountRequestDto;
use App\Modules\Account\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends AbstractController {
    #[Route(path: '/register', methods: [Request::METHOD_POST])]
    public function createAccount(
        #[MapRequestPayload] CreateAccountRequestDto $dto,
        Service $accountService
    ): JsonResponse
    {
        $response = $accountService->save($dto);

        return new JsonResponse($response->getMessage(), $response->getCode());
    }

	/**
	 * Leave this method empty. It just needs to be here as a referenceable method. Symfony handles the rest.
	 * Make a POST request against /login with the properties 'username' and 'password' in the json.
	 */
	#[Route(path: '/login', methods: [Request::METHOD_POST])]
    public function login() {}

    #[Route(path: "/account", methods: [Request::METHOD_PUT])]
    public function updateAccountData(
        #[MapRequestPayload] UpdateAccountRequestDto $dto,
        Service $accountService
    ): JsonResponse
    {
        if (
            empty($dto->emailAddress) &&
            empty($dto->firstName) &&
            empty($dto->surname) &&
            empty($dto->password)
        ) {
            return new JsonResponse("NoDataProvided", 400);
        }
        $response = $accountService->update($dto);
        return new JsonResponse($response->getMessage(), $response->getCode());
    }

    #[Route(path: '/account/my', methods: [Request::METHOD_GET])]
    public function getAccountData(Service $accountService): JsonResponse
    {
        $account = $accountService->getAccountByAuthId();

        $response = new AccountDataResponseDto(
            $account->emailAddress,
            $account->firstName,
            $account->surname,
            $account->createdAt,
            $account->updatedAt
        );

        return new JsonResponse($response);
    }
}