<?php

declare(strict_types=1);

namespace App\States\Publication;

use App\Enums\PublicationStatus;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\Publication;
use App\Models\User;

abstract class PublicationState
{
    public function __construct(
        protected Publication $publication
    ) {}

    /**
     * Get the corresponding status enum for this state.
     */
    abstract public function status(): PublicationStatus;

    /**
     * Determine if a transition to the target status is allowed from this state.
     */
    abstract public function canTransitionTo(PublicationStatus $target): bool;

    /**
     * Approve publication.
     *
     * @throws InvalidStateTransitionException
     */
    public function approve(User $actor, ?string $notes = null): void
    {
        throw new InvalidStateTransitionException(
            "Tidak dapat menyetujui publikasi dari status [{$this->status()->value}]."
        );
    }

    /**
     * Reject publication with notes/reason.
     *
     * @throws InvalidStateTransitionException
     */
    public function reject(User $actor, string $reason): void
    {
        throw new InvalidStateTransitionException(
            "Tidak dapat menolak publikasi dari status [{$this->status()->value}]."
        );
    }

    /**
     * Resubmit publication (e.g. after revisions by submitter).
     *
     * @throws InvalidStateTransitionException
     */
    public function resubmit(User $actor, ?string $notes = null): void
    {
        throw new InvalidStateTransitionException(
            "Tidak dapat mengajukan ulang publikasi dari status [{$this->status()->value}]."
        );
    }

    /**
     * General status transition router.
     *
     * @throws InvalidStateTransitionException
     */
    public function transitionTo(PublicationStatus $target, User $actor, ?string $notes = null): void
    {
        match ($target) {
            PublicationStatus::APPROVED => $this->approve($actor, $notes),
            PublicationStatus::REJECTED => $this->reject($actor, (string) $notes),
            PublicationStatus::PENDING => $this->resubmit($actor, $notes),
        };
    }
}
