<?php

namespace App\Models;

use App\Models\Concerns\HasRouteSlug;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Village;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class Assembly extends Model
{
    use HasRouteSlug;

    /** Kolom sumber slug URL — lihat HasRouteSlug. */
    public const ROUTE_SLUG_SOURCE = 'nama_majelis';

    protected $guarded = [];

    public function schedule()
    {
        return $this->hasMany(Schedule::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getGambarThumbUrlAttribute()
    {
        if ($this->gambar) {
            // Ganti 'large' jadi 'thumb' pada path
            $thumbPath = str_replace('large', 'thumb', $this->gambar);
            // Kembalikan URL lengkap siap pakai
            return Storage::url($thumbPath);
        }

        // Kembalikan placeholder atau null jika tidak ada gambar
        return null;
    }

    // Cara pakainya nanti: $majelis->gambar_large_url
    public function getGambarLargeUrlAttribute()
    {
        return $this->gambar ? Storage::url($this->gambar) : null;
    }

    public function province()
    {
        // Parameter: (Model Tujuan, foreign_key_lokal, owner_key_di_tabel_tujuan)
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /**
     * Relasi ke Kota/Kabupaten
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_code', 'code');
    }

    /**
     * Relasi ke Kecamatan
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    /**
     * Relasi ke Desa/Kelurahan
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function ramadhanSchedules()
    {
        return $this->hasMany(RamadhanSchedule::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'assembly_user');
    }

    public function getLeaderNameAttribute()
    {
        return $this->teacher?->name ?? $this->custom_leader_name;
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contribution()
    {
        return $this->morphOne(Contribution::class, 'contributable');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('contribution_status')
              ->orWhere('contribution_status', 'approved');
        });
    }

    /**
     * Konten yang belum/tidak disetujui hanya boleh dibuka oleh pemiliknya
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

        return $user->id === $this->user_id || $user->hasRole('Super Admin');
    }
}
