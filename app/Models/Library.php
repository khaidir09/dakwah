<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'file_path',
        'cover_image',
        'price_type',
        'is_active',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
