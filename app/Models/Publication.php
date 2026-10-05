<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PublicationStatus;
use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    /** @use HasFactory<PublicationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'submitter_id',
        'category_id',
        'doi',
        'title',
        'abstract',
        'journal_name',
        'publication_year',
        'volume',
        'issue',
        'pages',
        'status',
        'file_path',
        'original_filename',
        'file_size_bytes',
        'metadata_api',
        'citation_count',
        'is_public',
        'published_at',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'publication_year' => 'integer',
            'file_size_bytes' => 'integer',
            'metadata_api' => 'array',
            'citation_count' => 'integer',
            'is_public' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The submitting user (Dosen / Author).
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_id');
    }

    /**
     * The research category / field.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * All authors associated with this publication (internal and external), ordered by author_order.
     */
    public function authors(): HasMany
    {
        return $this->hasMany(PublicationAuthor::class, 'publication_id')->orderBy('author_order');
    }

    /**
     * Internal users who are co-authors.
     */
    public function authorUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'publication_authors', 'publication_id', 'user_id')
            ->withPivot(['author_name', 'author_email', 'affiliation', 'author_role', 'author_order', 'is_corresponding', 'is_external'])
            ->withTimestamps();
    }

    /**
     * Verification audit log entries.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'publication_id')->orderByDesc('created_at');
    }

    /**
     * Download log entries.
     */
    public function downloadLogs(): HasMany
    {
        return $this->hasMany(DownloadLog::class, 'publication_id')->orderByDesc('downloaded_at');
    }

    /**
     * Check if publication is approved/published.
     */
    public function isApproved(): bool
    {
        return $this->status === PublicationStatus::APPROVED;
    }

    /**
     * Check if publication is pending review.
     */
    public function isPending(): bool
    {
        return $this->status === PublicationStatus::PENDING;
    }

    /**
     * Check if publication was rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === PublicationStatus::REJECTED;
    }

    /**
     * Get first author's name if available.
     */
    public function getFirstAuthorNameAttribute(): ?string
    {
        return $this->authors->first()?->author_name ?? $this->submitter?->name;
    }

    /**
     * Scope to approved publications.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::APPROVED->value);
    }

    /**
     * Scope to pending publications.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::PENDING->value);
    }

    /**
     * Scope to rejected publications.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::REJECTED->value);
    }

    /**
     * Scope to public publications.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter by publication year.
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('publication_year', $year);
    }

    /**
     * Scope search across title, abstract, DOI, journal name, and author names.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('abstract', 'like', "%{$term}%")
                ->orWhere('doi', 'like', "%{$term}%")
                ->orWhere('journal_name', 'like', "%{$term}%")
                ->orWhereHas('authors', function (Builder $authorQuery) use ($term) {
                    $authorQuery->where('author_name', 'like', "%{$term}%");
                });
        });
    }
}
