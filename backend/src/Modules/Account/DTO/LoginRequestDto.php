<?php declare(strict_types=1);

namespace App\Modules\Account\DTO;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginRequestDto
{
	public function __construct(
		#[NotBlank()]
		#[Email()]
		public readonly string $emailAddress,
		#[NotBlank()]
		public readonly string $password
	)
	{
		
	}
}