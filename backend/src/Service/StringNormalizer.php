<?php declare(strict_types=1);

namespace App\Service;

final class StringNormalizer {
    public function asNoun(string $noun): string
    {
        return ucfirst(strtolower(preg_replace('/[^A-Za-zÄÖÜäöüßéèêëàâôçÇ\'’]+/', "", $noun)));
    }
}