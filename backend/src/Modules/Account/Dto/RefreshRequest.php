<?php

declare(strict_types=1);

namespace App\Modules\Account\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class RefreshRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 256)]
    public string $refreshToken = '';
}
