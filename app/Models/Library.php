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
        'visit_count',
        'like_count',
        'podcast_audio_path',
        'podcast_metadata',
    ];

    protected $casts = [
        'podcast_metadata' => 'array',
    ];

    public function episodes()
    {
        return $this->hasMany(LibraryEpisode::class)->orderBy('sort_order')->orderBy('created_at');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
