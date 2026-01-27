<?php

namespace App\Livewire;

use App\Models\Biography;
use Livewire\Component;
use Livewire\WithPagination;

class ListBiography extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $biographies_count = Biography::count();
        $query = Biography::query();

        $query->latest();

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama', 'like', $searchTerm)
                         ->orWhere('deskripsi', 'like', $searchTerm);
            });
        }

        $biographies = $query->simplePaginate($this->paginate);

        return view('livewire.list-biography', [
            'biographies_count' => $biographies_count,
            'biographies' => $biographies
        ]);
    }
}
