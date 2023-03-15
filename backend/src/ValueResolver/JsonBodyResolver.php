<?php declare(strict_types=1);

namespace App\ValueResolver;

use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class JsonBodyResolver implements ValueResolverInterface
{
    private const argument = 'data';

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if ($argument->getType() === 'array' && $argument->getName() === self::argument) {
            try {
                return  [$request->toArray()];
            } catch (JsonException) {
                return [[]];
            }
        }

        return [];
    }
}