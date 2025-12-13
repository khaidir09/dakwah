<?php

namespace App\Livewire;

use App\Models\Video;
use Livewire\Component;
use Livewire\WithPagination;

class ListVideo extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $category;

    protected $updatesQueryString = ['search', 'category'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->category = request()->query('category', $this->category);
    }

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
        $videos_count = Video::count();
        $query = Video::latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('title', 'like', $searchTerm)->orWhere('category', 'like', $searchTerm);
            });
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        // Ambil hasil akhir dengan paginasi
        $videos = $query->simplePaginate($this->paginate);

        return view('livewire.list-video', [
            'videos_count' => $videos_count,
            'videos' => $videos
        ]);
    }
}
