<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ScopusQuartile;
use App\Enums\SintaTier;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sinta_tier',
        'scopus_quartile',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sinta_tier' => SintaTier::class,
            'scopus_quartile' => ScopusQuartile::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Publications in this category.
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'category_id');
    }

    /**
     * Scope to active categories only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to Scopus indexed categories (Q1-Q4).
     */
    public function scopeScopusIndexed(Builder $query): Builder
    {
        return $query->whereNotNull('scopus_quartile')
            ->where('scopus_quartile', '!=', ScopusQuartile::NON_SCOPUS->value);
    }

    /**
     * Scope to SINTA accredited categories (S1-S6).
     */
    public function scopeSintaIndexed(Builder $query): Builder
    {
        return $query->whereNotNull('sinta_tier')
            ->where('sinta_tier', '!=', SintaTier::NON_SINTA->value);
    }
}
