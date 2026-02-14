<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScientificArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'foundation_id',
        'title',
        'subtitle',
        'slug',
        'author_name',
        'category',
        'published_at',
        'cover_image',
        'notebook_id',
        'status',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function foundation()
    {
        return $this->belongsTo(Foundation::class);
    }

    public function sections()
    {
        return $this->hasMany(ArticleSection::class, 'article_id');
    }

    public function citations()
    {
        return $this->hasMany(ArticleCitation::class, 'article_id');
    }

    public function bibliographies()
    {
        return $this->hasMany(ArticleBibliography::class, 'article_id');
    }
}
