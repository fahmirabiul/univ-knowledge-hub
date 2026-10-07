<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\PublicationStatus;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\AuditLog;
use App\Models\Publication;
use Illuminate\Support\Facades\Auth;

class PublicationObserver
{
    /**
     * Handle the Publication "updating" event.
     * Validate status transition legality against the State Pattern before saving.
     *
     * @throws InvalidStateTransitionException
     */
    public function updating(Publication $publication): void
    {
        if ($publication->isDirty('status')) {
            $rawOriginal = $publication->getOriginal('status');

            $from = $rawOriginal instanceof PublicationStatus
                ? $rawOriginal
                : PublicationStatus::tryFrom((string) $rawOriginal);

            $to = $publication->status;

            if ($from !== null && ! $publication->stateFor($from)->canTransitionTo($to)) {
                throw new InvalidStateTransitionException(
                    "Transisi status publikasi dari [{$from->value}] ke [{$to->value}] tidak diizinkan oleh sistem."
                );
            }
        }
    }

    /**
     * Handle the Publication "updated" event.
     * Record transition audit trail to audit_logs table.
     */
    public function updated(Publication $publication): void
    {
        if ($publication->wasChanged('status')) {
            $rawOriginal = $publication->getOriginal('status');

            $statusBefore = $rawOriginal instanceof PublicationStatus
                ? $rawOriginal->value
                : (string) $rawOriginal;

            $statusAfter = $publication->status->value;

            $actorId = $publication->currentActor?->id ?? Auth::id() ?? null;
            $notes = $publication->auditNotes ?? $publication->rejection_reason ?? null;

            AuditLog::create([
                'publication_id' => $publication->id,
                'actor_id' => $actorId,
                'status_before' => $statusBefore,
                'status_after' => $statusAfter,
                'notes' => $notes,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);

            // Reset transient state
            $publication->auditNotes = null;
            $publication->currentActor = null;
        }
    }
}
