<?php

namespace App\Models;

use App\Models\Concerns\HasRouteSlug;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Village;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class Event extends Model
{
    use HasRouteSlug;

    public const ROUTE_SLUG_SOURCE = 'name';

    protected $guarded = [];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
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

    public function contributions()
    {
        return $this->morphMany(Contribution::class, 'contributable');
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posterGenerations()
    {
        return $this->hasMany(EventPosterGeneration::class);
    }

    /**
     * Poster hasil AI disimpan dua varian (`events/large/...` dan `events/thumb/...`),
     * sedangkan poster yang diunggah manual — termasuk seluruh data lama — hanya satu
     * berkas flat `events/...`. Accessor ini menyerap perbedaan itu agar view tidak
     * perlu tahu asal posternya.
     */
    public function getImageThumbUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::url(
            str_contains($this->image, '/large/')
                ? str_replace('/large/', '/thumb/', $this->image)
                : $this->image
        );
    }

    public function getImageLargeUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    /**
     * Acara yang sudah dilepas ke publik: pernah dimoderasi (atau dibuat langsung
     * oleh Super Admin, yang mengisi `moderated_at` tanpa mengubah `status`) dan
     * tidak ditolak.
     *
     * `moderated_at` saja tidak cukup: ModerasiController::revokeEvent() juga
     * mengisinya saat menolak, sehingga acara yang ditolak ikut lolos.
     * `status = 'approved'` saja juga tidak cukup: acara buatan Super Admin
     * tetap bernilai default 'pending' sehingga akan hilang dari kanal publik.
     */
    public function scopePubliclyVisible($query)
    {
        return $query->whereNotNull('moderated_at')
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 'rejected');
            });
    }

    /**
     * Syarat halaman detail publik, yang lebih ketat daripada daftar acara:
     * selain lolos moderasi, acara juga harus terbuka untuk umum. Acara "Khusus"
     * sengaja tetap tampil di daftar (keputusan produk lama) tetapi tidak
     * mendapat halaman sendiri yang dapat diindeks.
     *
     * Pemilik dan Super Admin tetap dapat membukanya sebagai pratinjau, sama
     * seperti Teacher, Assembly, dan Schedule.
     */
    public function isVisibleTo(?User $user): bool
    {
        if ($this->isPubliclyVisible()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->id === $this->user_id || $user->hasRole('Super Admin');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->moderated_at !== null
            && $this->status !== 'rejected'
            && $this->access === 'Umum';
    }
}
