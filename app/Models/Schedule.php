<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    protected $guarded = [];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getWaktuFormattedAttribute()
    {
        // Cek jika 'waktu' ada isinya sebelum mem-format
        if ($this->waktu) {
            // Pilih salah satu format yang kamu suka dari atas
            // Contoh ini menggunakan Rekomendasi 1 (AM/PM)
            return Carbon::parse($this->waktu)->locale('id')->isoFormat('LT');
            
            // Jika suka format Indonesia (Rekomendasi 3):
            // return Carbon::parse($this->waktu)->locale('id')->isoFormat('LT');
        }
        return 'N/A'; // Tampilkan ini jika waktunya null
    }
}
