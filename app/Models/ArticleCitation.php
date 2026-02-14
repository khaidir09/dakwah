<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleCitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'type',
        'source_text_arabic',
        'translation',
        'reference',
    ];

    public function scientificArticle()
    {
        return $this->belongsTo(ScientificArticle::class, 'article_id');
    }
}
