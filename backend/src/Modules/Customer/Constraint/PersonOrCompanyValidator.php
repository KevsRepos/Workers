<?php

declare(strict_types=1);

namespace App\Modules\Customer\Constraint;

use App\Modules\Customer\Dto\CustomerData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PersonOrCompanyValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PersonOrCompany) {
            throw new UnexpectedTypeException($constraint, PersonOrCompany::class);
        }
        if (!$value instanceof CustomerData) {
            return;
        }

        if ($value->company) {
            if (trim((string) $value->companyName) === '') {
                $this->context->buildViolation('Company name is required for companies.')->atPath('companyName')->addViolation();
            }

            return;
        }

        if (trim((string) $value->firstName) === '') {
            $this->context->buildViolation('First name is required.')->atPath('firstName')->addViolation();
        }
        if (trim((string) $value->surname) === '') {
            $this->context->buildViolation('Surname is required.')->atPath('surname')->addViolation();
        }
    }
}
