<?php

declare(strict_types=1);

namespace App\Modules\Account\Dto;

use App\Shared\Validation\StrongPassword;

final class ResetPasswordRequest
{
    #[StrongPassword]
    public string $password = '';
}
