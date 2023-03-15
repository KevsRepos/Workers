<?php declare(strict_types=1);

namespace App\Service;

final class StringNormalizer {
    public function asNoun(string $noun): string
    {
        return ucfirst(strtolower(str_replace(' ', '', trim($noun))));
    }
}