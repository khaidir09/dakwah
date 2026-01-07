<?php

namespace App\Models;

use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Village;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class Assembly extends Model
{
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

    public function followers()
    {
        return $this->belongsToMany(User::class, 'assembly_user');
    }
}
