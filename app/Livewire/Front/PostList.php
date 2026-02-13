<?php

namespace App\Livewire\Front;

use App\Models\Label;
use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    public $label = '';
    public $search = '';

    protected $queryString = [
        'label' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingLabel()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Post::published()->with('user', 'labels');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->label) {
            $query->whereHas('labels', function($q) {
                $q->where('slug', $this->label);
            });
        }

        $posts = $query->latest('published_at')->paginate(12);
        $labels = Label::has('posts')->get();

        return view('livewire.front.post-list', [
            'posts' => $posts,
            'labels' => $labels,
        ]);
    }
}
