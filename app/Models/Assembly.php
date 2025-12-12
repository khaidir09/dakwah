<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
}
