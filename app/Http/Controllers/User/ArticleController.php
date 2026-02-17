<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ScientificArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function download($slug)
    {
        $article = ScientificArticle::where('status', 'PUBLISHED')
            ->where('slug', $slug)
            ->firstOrFail();

        if (!$article->file_path || !Storage::exists($article->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::download($article->file_path);
    }
}
