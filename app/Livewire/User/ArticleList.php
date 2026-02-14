<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\ScientificArticle;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArticleList extends Component
{
    use WithPagination;

    public $paginate = 10;

    public $confirmingDeletion = false;
    public $article_id_to_delete;

    public function confirmDelete($articleId)
    {
        $this->article_id_to_delete = $articleId;
        $this->confirmingDeletion = true;
    }

    public function deleteArticle()
    {
        if ($this->article_id_to_delete) {
            $article = ScientificArticle::find($this->article_id_to_delete);

            if ($article) {
                // Verify ownership
                $userFoundations = Auth::user()->foundations->pluck('id')->toArray();
                if (in_array($article->foundation_id, $userFoundations)) {
                    if ($article->cover_image) {
                        Storage::delete($article->cover_image);
                    }
                    $article->delete();
                    session()->flash('message', 'Artikel berhasil dihapus.');
                }
            }
        }

        $this->confirmingDeletion = false;
        $this->article_id_to_delete = null;
    }

    public function render()
    {
        $userId = Auth::id();

        $articles_count = ScientificArticle::whereHas('foundation', function ($query) use ($userId) {
            $query->whereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            });
        })->count();

        $query = ScientificArticle::with('foundation')
            ->whereHas('foundation', function ($query) use ($userId) {
                $query->whereHas('users', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                });
            })
            ->orderBy('created_at', 'desc');

        $articles = $query->simplePaginate($this->paginate);

        return view('livewire.user.article-list', [
            'articles_count' => $articles_count,
            'articles' => $articles
        ]);
    }
}
