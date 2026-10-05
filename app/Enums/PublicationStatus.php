<?php

declare(strict_types=1);

namespace App\Enums;

enum PublicationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::APPROVED => 'Disetujui / Terpublikasi',
            self::REJECTED => 'Ditolak',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => '#FF9F43',  // Status: Pending token in DESIGN.md
            self::APPROVED => '#28C76F', // Status: Approved token in DESIGN.md
            self::REJECTED => '#EA5455', // Status: Rejected token in DESIGN.md
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
}
