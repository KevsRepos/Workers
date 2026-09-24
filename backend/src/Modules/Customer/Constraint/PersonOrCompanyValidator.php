<?php declare(strict_types=1);

namespace App\Modules\Customer\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PersonOrCompanyValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PersonOrCompany) {
            throw new UnexpectedTypeException($constraint, PersonOrCompany::class);
        }

        $hasPersonName = !empty($value->firstName) && !empty($value->surname);
        $hasCompany = !empty($value->companyName);

        if ($hasPersonName === $hasCompany) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
