<?php

declare(strict_types=1);

namespace App\States\Publication;

use App\Enums\PublicationStatus;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\User;

class RejectedState extends PublicationState
{
    public function status(): PublicationStatus
    {
        return PublicationStatus::REJECTED;
    }

    public function canTransitionTo(PublicationStatus $target): bool
    {
        // Enforce TDD constraint: Rejected -> Approved directly is strictly prohibited
        return $target === PublicationStatus::PENDING;
    }

    public function approve(User $actor, ?string $notes = null): void
    {
        throw new InvalidStateTransitionException(
            'Publikasi yang berstatus ditolak tidak dapat langsung disetujui tanpa proses perbaikan naskah dan pengajuan ulang.'
        );
    }

    public function reject(User $actor, string $reason): void
    {
        throw new InvalidStateTransitionException(
            'Publikasi sudah berstatus ditolak.'
        );
    }

    public function resubmit(User $actor, ?string $notes = null): void
    {
        if ($actor->id !== $this->publication->submitter_id && ! $actor->isAdminLPPM()) {
            throw new InvalidStateTransitionException(
                'Hanya dosen pengusul naskah yang berwenang mengajukan ulang naskah yang ditolak.'
            );
        }

        $this->publication->currentActor = $actor;
        $this->publication->auditNotes = $notes ?? 'Naskah perbaikan diajukan ulang oleh dosen pengusul.';
        $this->publication->status = PublicationStatus::PENDING;
        $this->publication->save();
    }
}
