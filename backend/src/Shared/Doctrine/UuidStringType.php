<?php

declare(strict_types=1);

namespace App\Shared\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\Uuid;

/**
 * Stores UUIDs as BINARY(16) while exposing them as canonical RFC 4122 strings in PHP.
 */
final class UuidStringType extends Type
{
    public const string NAME = 'app_uuid';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL(['length' => 16, 'fixed' => true]);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (\is_resource($value)) {
            $value = stream_get_contents($value);
        }

        if (!\is_string($value)) {
            throw new ConversionException('Unexpected UUID value from database.');
        }

        return Uuid::fromBinary($value)->toRfc4122();
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value->toBinary();
        }

        if (\is_string($value) && Uuid::isValid($value)) {
            return Uuid::fromString($value)->toBinary();
        }

        throw new ConversionException('Invalid UUID value.');
    }
}
