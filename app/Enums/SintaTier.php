<?php

declare(strict_types=1);

namespace App\Enums;

enum SintaTier: string
{
    case S1 = 'S1';
    case S2 = 'S2';
    case S3 = 'S3';
    case S4 = 'S4';
    case S5 = 'S5';
    case S6 = 'S6';
    case NON_SINTA = 'Non-SINTA';

    public function label(): string
    {
        return match ($this) {
            self::NON_SINTA => 'Non-SINTA',
            default => "SINTA {$this->value}",
        };
    }

    public function isIndexed(): bool
    {
        return $this !== self::NON_SINTA;
    }
}
