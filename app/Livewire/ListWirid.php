<?php

namespace App\Livewire;

use App\Models\Wirid;
use Livewire\Component;
use Livewire\WithPagination;

class ListWirid extends Component
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
        $wirids_count = Wirid::count();
        $query = Wirid::latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama', 'like', $searchTerm);
            });
        }

        // Ambil hasil akhir dengan paginasi
        $wirids = $query->simplePaginate($this->paginate);

        return view('livewire.list-wirid', [
            'wirids_count' => $wirids_count,
            'wirids' => $wirids
        ]);
    }
}
