<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthorRole: string
{
    case FIRST_AUTHOR = 'penulis_pertama';
    case FIRST_CORRESPONDING = 'pertama_korespondensi';
    case CORRESPONDING = 'korespondensi';
    case CO_AUTHOR = 'pendamping';

    public function label(): string
    {
        return match ($this) {
            self::FIRST_AUTHOR => 'Penulis Pertama',
            self::FIRST_CORRESPONDING => 'Penulis Pertama & Korespondensi',
            self::CORRESPONDING => 'Penulis Korespondensi',
            self::CO_AUTHOR => 'Penulis Pendamping / Anggota',
        };
    }

    public function isCorresponding(): bool
    {
        return in_array($this, [self::FIRST_CORRESPONDING, self::CORRESPONDING], true);
    }

    public function isFirstAuthor(): bool
    {
        return in_array($this, [self::FIRST_AUTHOR, self::FIRST_CORRESPONDING], true);
    }
}
