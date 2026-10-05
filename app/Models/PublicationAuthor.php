<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AuthorRole;
use Database\Factories\PublicationAuthorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationAuthor extends Model
{
    /** @use HasFactory<PublicationAuthorFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'publication_authors';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'publication_id',
        'user_id',
        'author_name',
        'author_email',
        'affiliation',
        'author_role',
        'author_order',
        'is_corresponding',
        'is_external',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'author_role' => AuthorRole::class,
            'author_order' => 'integer',
            'is_corresponding' => 'boolean',
            'is_external' => 'boolean',
        ];
    }

    /**
     * The publication this author belongs to.
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    /**
     * The local user account if author is internal (Dosen/Mahasiswa).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to external authors.
     */
    public function scopeExternal(Builder $query): Builder
    {
        return $query->where('is_external', true);
    }

    /**
     * Scope to internal authors.
     */
    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('is_external', false);
    }

    /**
     * Scope to corresponding authors.
     */
    public function scopeCorresponding(Builder $query): Builder
    {
        return $query->where('is_corresponding', true);
    }

    /**
     * Scope to order by author sequence.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('author_order');
    }
}
