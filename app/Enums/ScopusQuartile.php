<?php

declare(strict_types=1);

namespace App\Enums;

enum ScopusQuartile: string
{
    case Q1 = 'Q1';
    case Q2 = 'Q2';
    case Q3 = 'Q3';
    case Q4 = 'Q4';
    case NON_SCOPUS = 'Non-Scopus';

    public function label(): string
    {
        return match ($this) {
            self::NON_SCOPUS => 'Non-Scopus',
            default => "Scopus {$this->value}",
        };
    }

    public function isIndexed(): bool
    {
        return $this !== self::NON_SCOPUS;
    }
}
