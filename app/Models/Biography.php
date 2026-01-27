<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biography extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'slug',
        'foto',
        'deskripsi',
        'source',
        'maps',
        'tanggal_wafat_masehi',
        'tanggal_wafat_hijriah',
    ];

    protected $casts = [
        'source' => 'array',
    ];
}
