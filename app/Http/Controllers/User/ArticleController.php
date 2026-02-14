<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ScientificArticle;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function detail($slug)
    {
        $article = ScientificArticle::where('status', 'PUBLISHED')
            ->where('slug', $slug)
            ->with(['foundation', 'sections' => function($q) {
                $q->orderBy('order');
            }, 'citations', 'bibliography'])
            ->firstOrFail();

        return view('pages.user.article.detail', compact('article'));
    }
}
