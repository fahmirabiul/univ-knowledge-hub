<?php

declare(strict_types=1);

namespace App\States\Publication;

use App\Enums\PublicationStatus;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\User;

class PendingState extends PublicationState
{
    public function status(): PublicationStatus
    {
        return PublicationStatus::PENDING;
    }

    public function canTransitionTo(PublicationStatus $target): bool
    {
        return $target === PublicationStatus::APPROVED || $target === PublicationStatus::REJECTED;
    }

    public function approve(User $actor, ?string $notes = null): void
    {
        if (! $actor->isAdminLPPM()) {
            throw new InvalidStateTransitionException(
                'Hanya Admin LPPM yang memiliki wewenang untuk menyetujui publikasi ilmiah.'
            );
        }

        $this->publication->currentActor = $actor;
        $this->publication->auditNotes = $notes ?? 'Publikasi ilmiah telah diverifikasi dan disetujui oleh LPPM.';
        $this->publication->status = PublicationStatus::APPROVED;
        $this->publication->published_at = now();
        $this->publication->rejection_reason = null;
        $this->publication->save();
    }

    public function reject(User $actor, string $reason): void
    {
        if (! $actor->isAdminLPPM()) {
            throw new InvalidStateTransitionException(
                'Hanya Admin LPPM yang memiliki wewenang untuk menolak publikasi ilmiah.'
            );
        }

        $trimmedReason = trim($reason);
        if ($trimmedReason === '') {
            throw new InvalidStateTransitionException(
                'Alasan penolakan publikasi wajib diisi.'
            );
        }

        $this->publication->currentActor = $actor;
        $this->publication->auditNotes = $trimmedReason;
        $this->publication->status = PublicationStatus::REJECTED;
        $this->publication->rejection_reason = $trimmedReason;
        $this->publication->save();
    }

    public function resubmit(User $actor, ?string $notes = null): void
    {
        throw new InvalidStateTransitionException(
            'Publikasi sudah dalam status antrean verifikasi LPPM (pending).'
        );
    }
}
