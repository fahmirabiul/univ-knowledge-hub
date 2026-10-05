<?php

declare(strict_types=1);

namespace App\Enums;

enum AcademicStatus: string
{
    case AKTIF = 'aktif';
    case CUTI = 'cuti';
    case NONAKTIF = 'nonaktif';
    case ALUMNI = 'alumni';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::CUTI => 'Cuti',
            self::NONAKTIF => 'Non-Aktif',
            self::ALUMNI => 'Alumni',
        };
    }

    public function isEligible(): bool
    {
        return $this === self::AKTIF;
    }
}
