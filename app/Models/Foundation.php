<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foundation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_path',
        'website_url',
    ];

    public function scientificArticles()
    {
        return $this->hasMany(ScientificArticle::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'foundation_user');
    }
}
