<?php declare(strict_types=1);

namespace App\Controller\Account;

use App\DTO\DataCreateAccountRequest;
use App\Service\AccountAsserter;
use App\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController {
    #[Route(path: '/accounts', methods: [Request::METHOD_POST])]
    public function createAccount(array $data, AccountAsserter $accountAsserter, AccountService $accountService): JsonResponse
    {
        
        $accountAsserter
        ->setEmailAddress($data['emailAddress'] ?? "")
        ->setFirstName($data['firstName'] ?? "")
        ->setSurname($data['surname'] ?? "")
        ->setRawPassword($data['password'] ?? "")
        ->setHashedPassword();

        $errors = $accountAsserter->validate();

        if(count($accountAsserter->validate())) {
            return new JsonResponse($errors->__toString(), 400);
        }

        $userRequest = new DataCreateAccountRequest(
            $accountAsserter->getEmailAddress(),
            $accountAsserter->getFirstName(),
            $accountAsserter->getSurname(),
            $accountAsserter->getHashedPassword()
        );

        return new JsonResponse($accountService->save($userRequest));
    }
}