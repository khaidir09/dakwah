<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public function scopePubliclyVisible($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('contribution_status')
                ->orWhere('contribution_status', 'approved');
        });
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
