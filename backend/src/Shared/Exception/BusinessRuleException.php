<?php

declare(strict_types=1);

namespace App\Shared\Exception;

/**
 * A syntactically valid request violates a business rule.
 */
final class BusinessRuleException extends ApiException
{
    /**
     * @param list<array{field: string, message: string, code?: string}> $errors
     */
    public static function withErrors(string $detail, array $errors): self
    {
        return new self($detail, ['errors' => $errors]);
    }

    public static function forField(string $field, string $message, ?string $code = null): self
    {
        $error = ['field' => $field, 'message' => $message];
        if ($code !== null) {
            $error['code'] = $code;
        }

        return new self($message, ['errors' => [$error]]);
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public function getType(): string
    {
        return 'business-rule-violation';
    }

    public function getTitle(): string
    {
        return 'Business rule violated';
    }
}
