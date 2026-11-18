<?php

declare(strict_types=1);

namespace App\Modules\Account\Dto;

use App\Modules\Account\Role;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateAccountRequest
{
    #[Assert\NotBlank]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT)]
    #[Assert\Length(max: 180)]
    public string $emailAddress = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $firstName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $surname = '';

    /** @var list<string> */
    #[Assert\All([new Assert\Choice(choices: Role::ASSIGNABLE)])]
    public array $roles = [Role::USER];

    public bool $active = true;
}
