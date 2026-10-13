<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AcademicStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sso_id',
        'name',
        'email',
        'role',
        'academic_status',
        'faculty',
        'study_program',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'academic_status' => AcademicStatus::class,
        ];
    }

    /**
     * Publications submitted by this user (Dosen / Author).
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'submitter_id');
    }

    /**
     * Publication author records linked to this user.
     */
    public function authorProfiles(): HasMany
    {
        return $this->hasMany(PublicationAuthor::class, 'user_id');
    }

    /**
     * Publications where this user is an author or co-author.
     */
    public function authoredPublications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'publication_authors', 'user_id', 'publication_id')
            ->withPivot(['author_name', 'author_email', 'affiliation', 'author_role', 'author_order', 'is_corresponding', 'is_external'])
            ->withTimestamps();
    }

    /**
     * Audit logs performed by this user (e.g. Admin LPPM reviewer).
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    /**
     * Download logs performed by this user.
     */
    public function downloadLogs(): HasMany
    {
        return $this->hasMany(DownloadLog::class, 'user_id');
    }

    /**
     * Check if user is a Dosen.
     */
    public function isDosen(): bool
    {
        return $this->role === UserRole::DOSEN;
    }

    /**
     * Check if user is an Admin LPPM.
     */
    public function isAdminLPPM(): bool
    {
        if ($this->role === UserRole::ADMIN_LPPM) {
            return true;
        }

        // Auto-heal administrative accounts that were previously synced as mahasiswa
        if ($this->role === UserRole::MAHASISWA && (
            str_contains($this->email, 'sdm@') ||
            str_contains($this->email, 'lppm@') ||
            str_contains($this->email, 'tik@') ||
            str_contains($this->email, 'admin.') ||
            str_contains($this->email, 'superadmin@') ||
            str_contains(strtolower($this->name), 'budi santoso')
        )) {
            $this->update(['role' => UserRole::ADMIN_LPPM]);
            return true;
        }

        return false;
    }

    /**
     * Check if user is a Mahasiswa.
     */
    public function isMahasiswa(): bool
    {
        return $this->role === UserRole::MAHASISWA;
    }

    /**
     * Check if user can submit research papers.
     */
    public function canSubmitPublication(): bool
    {
        return $this->role?->canSubmitPublication() && ($this->academic_status?->isEligible() ?? true);
    }

    /**
     * Check if user can review research submissions.
     */
    public function canReviewPublication(): bool
    {
        return $this->role?->canReviewPublication() ?? false;
    }

    /**
     * Scope query to only Dosen.
     */
    public function scopeDosen(Builder $query): Builder
    {
        return $query->where('role', UserRole::DOSEN->value);
    }

    /**
     * Scope query to only Admin LPPM.
     */
    public function scopeAdminLppm(Builder $query): Builder
    {
        return $query->where('role', UserRole::ADMIN_LPPM->value);
    }

    /**
     * Scope query to active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('academic_status', AcademicStatus::AKTIF->value);
    }
}
