<?php declare(strict_types=1);

namespace App\Modules\Account;

use App\Modules\Account\DTO\CreateAccountRequest;
use App\Modules\Account\Asserter;
use App\Modules\Account\DTO\LoginRequestDto;
use App\Modules\Account\DTO\SendAccountRequest;
use App\Modules\Account\DTO\UpdateAccountRequest;
use App\Modules\Account\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class Controller extends AbstractController {
    #[Route(path: '/register', methods: [Request::METHOD_POST, Request::METHOD_GET])]
    public function createAccount(array $data, Asserter $accountAsserter, Service $accountService): JsonResponse
    {
        $accountAsserter
        ->setEmailAddress($data['emailAddress'] ?? "")
        ->setFirstName($data['firstName'] ?? "")
        ->setSurname($data['surname'] ?? "")
        ->setRawPassword($data['password'] ?? "");

        $errors = $accountAsserter->validate();

        if($errors) return new JsonResponse($errors, 400);

        $userRequest = new CreateAccountRequest(
            $accountAsserter->getEmailAddress(),
            $accountAsserter->getFirstName(),
            $accountAsserter->getSurname(),
            $accountAsserter->getRawPassword()
        );

        $response = $accountService->save($userRequest);

        return new JsonResponse($response->getMessage(), $response->getCode());
    }

	/**
	 * Leave this method empty. It just needs to be here as a referenceable method. Symfony handles the rest.
	 * Make a POST request against /login with the properties 'username' and 'password' in the json.
	 */
	#[Route(path: '/login', methods: [Request::METHOD_POST])]
    public function login() {}

    #[Route(path: "/account", methods: [Request::METHOD_PUT])]
    public function updateAccountData(array $data, Asserter $accountAsserter, Service $accountService): JsonResponse
    {
        if(
            empty($data['emailAddress']) &&
            empty($data['firstName']) &&
            empty($data['surname']) &&
            empty($data['password'])
        ) return new JsonResponse("NoDataProvided", 400);

        !empty($data['emailAddress']) && $accountAsserter->setEmailAddress($data['emailAddress']);
        !empty($data['firstName']) && $accountAsserter->setFirstName($data['firstName']);
        !empty($data['surname']) && $accountAsserter->setSurname($data['surname']);
        !empty($data['password']) && $accountAsserter->setRawPassword($data['password']);

        $errors = $accountAsserter->validate();

        if($errors) return new JsonResponse($errors, 400);

        $update = new UpdateAccountRequest(
            $accountAsserter->getEmailAddress(),
            $accountAsserter->getFirstName(),
            $accountAsserter->getSurname(),
            $accountAsserter->getRawPassword()
        );

        $response = $accountService->update($update);

        return new JsonResponse($response->getMessage(), $response->getCode());
    }

    #[Route(path: '/account/my', methods: [Request::METHOD_GET])]
    public function getAccountData(Service $accountService): JsonResponse
    {
		return new JsonResponse(['hello' => 'world']);
        // $account = $accountService->getAccountByAuthId();

        // $response = new SendAccountRequest(
        //     $account->emailAddress,
        //     $account->firstName,
        //     $account->surname,
        //     $account->createdAt,
        //     $account->updatedAt
        // );

        // return new JsonResponse($response);
    }
}