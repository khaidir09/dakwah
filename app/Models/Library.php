<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Library extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'file_path',
        'preview_file_path',
        'cover_image',
        'price_type',
        'price',
        'payee_name',
        'author_share_percent',
        'is_active',
        'visit_count',
        'like_count',
        'podcast_audio_path',
        'podcast_metadata',
    ];

    protected $casts = [
        'podcast_metadata' => 'array',
        'price' => 'integer',
        'author_share_percent' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(LibraryPurchase::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(LibraryPayout::class);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function isFree(): bool
    {
        return $this->price_type !== 'paid';
    }

    public function isPaid(): bool
    {
        return $this->price_type === 'paid';
    }

    /**
     * Pratinjau gratis hanya bermakna untuk pustaka berbayar.
     */
    public function hasPreview(): bool
    {
        return $this->isPaid() && filled($this->preview_file_path);
    }

    /**
     * Apakah file pustaka dapat dibaca oleh user tertentu.
     * Gratis terbuka untuk semua; berbayar hanya untuk admin atau pemilik akses aktif.
     */
    public function isAccessibleBy(?User $user): bool
    {
        if ($this->isFree()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasActiveLibraryPurchase($this);
    }
}
