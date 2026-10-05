<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DownloadLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadLog extends Model
{
    /** @use HasFactory<DownloadLogFactory> */
    use HasFactory;

    /**
     * Disable standard timestamps since downloads track downloaded_at.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'publication_id',
        'user_id',
        'ip_address',
        'user_agent',
        'downloaded_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    /**
     * The publication that was downloaded.
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    /**
     * The user who downloaded the publication (null for anonymous/guest downloads).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
