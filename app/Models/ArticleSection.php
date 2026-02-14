<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'heading',
        'content',
        'order',
    ];

    public function scientificArticle()
    {
        return $this->belongsTo(ScientificArticle::class, 'article_id');
    }
}
