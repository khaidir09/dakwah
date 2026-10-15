<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;

class Teacher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'source' => 'array',
    ];

    public static function generateSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
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

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? Storage::url($this->foto) : null;
    }

    public function getFotoBersamaUrlAttribute(): ?string
    {
        return $this->foto_bersama ? Storage::url($this->foto_bersama) : null;
    }

    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }

    public function province()
    {
        // Parameter: (Model Tujuan, foreign_key_lokal, owner_key_di_tabel_tujuan)
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /**
     * Relasi ke Kota/Kabupaten
     *
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_code', 'code');
    }

    /**
     * Relasi ke Kecamatan
     *
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    /**
     * Relasi ke Desa/Kelurahan
     *
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributor_user_id');
    }

    public function contribution()
    {
        return $this->morphOne(Contribution::class, 'contributable');
    }

    public function books()
    {
        return $this->belongsToMany(Book::class)
            ->withPivot('role', 'sort_order')
            ->withTimestamps();
    }

    /**
     * Kitab aktif karya guru ini untuk halaman publik, satu entri per kitab.
     * Satu kitab bisa muncul lebih dari sekali bila guru memegang beberapa peran di dalamnya.
     */
    public function karyaPublik(): Collection
    {
        return $this->books()
            ->active()
            ->with('specialization')
            ->orderByDesc('books.created_at')
            ->get()
            ->groupBy('id')
            ->map(fn ($rows) => [
                'book' => $rows->first(),
                'roles' => $rows->map(fn ($book) => Book::ROLES[$book->pivot->role] ?? $book->pivot->role)->unique()->implode(', '),
            ])
            ->values();
    }

    /**
     * Status wafat diturunkan dari data wafat yang tercatat; guru yang wafatnya
     * belum dicatat akan terbaca "masih hidup". Logika ini harus sama persis
     * dengan scopeWafat()/scopeMasihHidup().
     */
    public function isWafat(): bool
    {
        return filled($this->wafat_masehi) || filled($this->wafat_hijriah_year);
    }

    /**
     * Tahun wafat untuk label singkat: Masehi bila ada, selain itu Hijriah.
     */
    public function tahunWafat(): ?string
    {
        if (filled($this->wafat_masehi)) {
            return Carbon::parse($this->wafat_masehi)->year.' M';
        }

        if (filled($this->wafat_hijriah_year)) {
            return $this->wafat_hijriah_year.' H';
        }

        return null;
    }

    public function scopeWafat($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('wafat_masehi')
                ->orWhereNotNull('wafat_hijriah_year');
        });
    }

    public function scopeMasihHidup($query)
    {
        return $query->whereNull('wafat_masehi')->whereNull('wafat_hijriah_year');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('contribution_status')
                ->orWhere('contribution_status', 'approved');
        });
    }

    /**
     * Apakah manaqib benar-benar berisi teks yang terbaca.
     *
     * Bukan sekadar `filled()`: editor WYSIWYG menyimpan `<p></p>` (kadang berisi
     * `&nbsp;`) untuk kolom yang dikosongkan lagi. Tanpa pemeriksaan ini, halaman
     * manaqib yang tampak kosong akan mengaku kanonik atas dirinya sendiri dan
     * masuk sitemap sebagai duplikat `/guru/{slug}`.
     */
    public function hasManaqib(): bool
    {
        $teks = html_entity_decode(strip_tags((string) $this->manaqib), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return (string) preg_replace('/[\s\x{00A0}]+/u', '', $teks) !== '';
    }

    /**
     * Konten yang belum/tidak disetujui hanya boleh dibuka oleh kontributor pemiliknya
     * (sebagai pratinjau) dan Super Admin. Untuk publik, halamannya harus 404.
     */
    public function isVisibleTo(?User $user): bool
    {
        if (in_array($this->contribution_status, [null, 'approved'], true)) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->id === $this->contributor_user_id || $user->hasRole('Super Admin');
    }
}
