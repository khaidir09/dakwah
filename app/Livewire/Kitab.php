<?php

namespace App\Livewire;

use App\Models\Book;
use Livewire\Component;
use Livewire\WithPagination;

class Kitab extends Component
{
    use WithPagination;

    public $paginate = 10;

    public $search;

    public $status = '';

    protected $updatesQueryString = ['search', 'status'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->status = request()->query('status', $this->status);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Book::with(['teachers', 'specialization'])->latest();

        if ($this->search) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('title_arabic', 'like', $searchTerm);
            });
        }

        if ($this->status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status === 'draft') {
            $query->where('is_active', false);
        }

        return view('livewire.kitab', [
            'books_count' => Book::count(),
            'books' => $query->simplePaginate($this->paginate),
            'roles' => Book::ROLES,
        ]);
    }
}
