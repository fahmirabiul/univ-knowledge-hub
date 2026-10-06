<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

class PublicationPolicy
{
    /**
     * Determine whether the user can view the publication.
     */
    public function view(User $user, Publication $publication): bool
    {
        if ($publication->isApproved() && $publication->is_public) {
            return true;
        }

        if ($user->isAdminLPPM() || $publication->submitter_id === $user->id) {
            return true;
        }

        return $publication->authorUsers->contains('id', $user->id);
    }

    /**
     * Determine whether the user can update the publication.
     */
    public function update(User $user, Publication $publication): bool
    {
        if ($user->isAdminLPPM()) {
            return true;
        }

        // Submitter can only edit if publication is pending or rejected
        if ($publication->submitter_id === $user->id && ! $publication->isApproved()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the publication.
     */
    public function delete(User $user, Publication $publication): bool
    {
        if ($user->isAdminLPPM()) {
            return true;
        }

        // Submitter can only delete if publication is not yet approved
        if ($publication->submitter_id === $user->id && ! $publication->isApproved()) {
            return true;
        }

        return false;
    }
}
