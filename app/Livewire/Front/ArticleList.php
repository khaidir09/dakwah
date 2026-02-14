<?php

namespace App\Livewire\Front;

use App\Models\ScientificArticle;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleList extends Component
{
    use WithPagination;

    public $category = '';
    public $search = '';

    protected $queryString = [
        'category' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ScientificArticle::where('status', 'PUBLISHED')
            ->with('foundation');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('author_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        $articles = $query->latest('published_at')->paginate(12);

        // Get distinct categories from published articles
        $categories = ScientificArticle::where('status', 'PUBLISHED')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.front.article-list', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }
}
