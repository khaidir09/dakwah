<?php

namespace App\Livewire;

use App\Models\Library;
use Livewire\Component;
use Livewire\WithPagination;

class ListLibrary extends Component
{
    use WithPagination;

    public $paginate = 12;
    public $search;
    public $category;
    public $price_type;

    protected $updatesQueryString = ['search', 'category', 'price_type'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->category = request()->query('category', $this->category);
        $this->price_type = request()->query('price_type', $this->price_type);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingPriceType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Library::query()->where('is_active', true);

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where('title', 'like', $searchTerm);
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->price_type) {
            $query->where('price_type', $this->price_type);
        }

        $libraries = $query->latest()->simplePaginate($this->paginate);
        $categories = Library::where('is_active', true)->select('category')->distinct()->pluck('category');

        return view('livewire.list-library', [
            'libraries' => $libraries,
            'categories' => $categories
        ]);
    }
}
