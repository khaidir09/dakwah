<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Book extends Model
{
    public const LANGUAGES = [
        'arab' => 'Arab',
        'arab_melayu' => 'Arab-Melayu (Jawi)',
        'banjar' => 'Banjar',
        'indonesia' => 'Indonesia',
        'lainnya' => 'Lainnya',
    ];

    public const PUBLICATION_STATUSES = [
        'manuskrip' => 'Manuskrip',
        'tercetak' => 'Tercetak',
        'hilang' => 'Hilang',
    ];

    public const ROLES = [
        'pengarang' => 'Pengarang',
        'pensyarah' => 'Pensyarah',
        'penerjemah' => 'Penerjemah',
        'pentahqiq' => 'Pentahqiq',
    ];

    protected $fillable = [
        'title',
        'title_arabic',
        'slug',
        'description',
        'specialization_id',
        'language',
        'cover_image',
        'publisher',
        'publication_status',
        'external_url',
        'library_id',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public static function generateSlug($title, $ignoreId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($ignoreId, function ($q) use ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        })->exists()) {
            $slug = $originalSlug.'-'.$count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class)
            ->withPivot('role', 'sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    public function getCoverThumbUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::url(str_replace('/large/', '/thumb/', $this->cover_image)) : null;
    }

    public function getLanguageLabelAttribute(): ?string
    {
        return self::LANGUAGES[$this->language] ?? null;
    }

    public function getPublicationStatusLabelAttribute(): ?string
    {
        return self::PUBLICATION_STATUSES[$this->publication_status] ?? null;
    }

    /**
     * Pengarang yang ditampilkan di kartu: pengarang pertama menurut urutan,
     * atau guru pertama bila kitab hanya punya pensyarah/penerjemah.
     * Mengandalkan relasi `teachers` yang sudah dimuat (dan sudah difilter publik).
     */
    public function primaryTeacher(): ?Teacher
    {
        return $this->teachers->firstWhere('pivot.role', 'pengarang') ?? $this->teachers->first();
    }

    public function isVisibleTo(?User $user): bool
    {
        if ($this->is_active) {
            return true;
        }

        return (bool) $user?->hasRole('Super Admin');
    }
}
