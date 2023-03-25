<?php declare(strict_types=1);

namespace App\Modules\Account;

use App\Modules\Account\DataCreateAccountRequest;
use App\Modules\Account\Asserter;
use App\Modules\Account\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class Controller extends AbstractController {
    #[Route(path: '/accounts', methods: [Request::METHOD_POST])]
    public function createAccount(array $data, Asserter $accountAsserter, Service $accountService): JsonResponse
    {
        $accountAsserter
        ->setEmailAddress($data['emailAddress'] ?? "")
        ->setFirstName($data['firstName'] ?? "")
        ->setSurname($data['surname'] ?? "")
        ->setRawPassword($data['password'] ?? "")
        ->setHashedPassword();

        $errors = $accountAsserter->validate();

        if(count($errors)) {
            return new JsonResponse($errors->__toString(), 400);
        }

        $userRequest = new DataCreateAccountRequest(
            $accountAsserter->getEmailAddress(),
            $accountAsserter->getFirstName(),
            $accountAsserter->getSurname(),
            $accountAsserter->getHashedPassword()
        );

        $response = $accountService->save($userRequest);

        return new JsonResponse($response->getMessage(), $response->getCode());
    }
}