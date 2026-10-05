<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case DOSEN = 'dosen';
    case MAHASISWA = 'mahasiswa';
    case ADMIN_LPPM = 'admin_lppm';
    case PUBLIC = 'public';

    public function label(): string
    {
        return match ($this) {
            self::DOSEN => 'Dosen / Peneliti',
            self::MAHASISWA => 'Mahasiswa',
            self::ADMIN_LPPM => 'Admin LPPM',
            self::PUBLIC => 'Pengunjung Publik',
        };
    }

    public function canSubmitPublication(): bool
    {
        return in_array($this, [self::DOSEN, self::ADMIN_LPPM], true);
    }

    public function canReviewPublication(): bool
    {
        return $this === self::ADMIN_LPPM;
    }
}
