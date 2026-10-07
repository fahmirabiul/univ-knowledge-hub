<?php

declare(strict_types=1);

namespace App\States\Publication;

use App\Enums\PublicationStatus;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\User;

class ApprovedState extends PublicationState
{
    public function status(): PublicationStatus
    {
        return PublicationStatus::APPROVED;
    }

    public function canTransitionTo(PublicationStatus $target): bool
    {
        return false;
    }

    public function approve(User $actor, ?string $notes = null): void
    {
        throw new InvalidStateTransitionException(
            'Publikasi sudah berstatus disetujui dan terpublikasi.'
        );
    }

    public function reject(User $actor, string $reason): void
    {
        throw new InvalidStateTransitionException(
            'Publikasi yang telah disetujui LPPM bersifat final dan tidak dapat diubah statusnya menjadi ditolak.'
        );
    }

    public function resubmit(User $actor, ?string $notes = null): void
    {
        throw new InvalidStateTransitionException(
            'Publikasi yang telah disetujui LPPM tidak dapat diajukan ulang.'
        );
    }
}
