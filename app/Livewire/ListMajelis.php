<?php

namespace App\Livewire;

use App\Models\Assembly;
use Livewire\Component;
use Livewire\WithPagination;

class ListMajelis extends Component
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
        $assemblies_count = Assembly::count();
        $query = Assembly::with('teacher')->latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama_majelis', 'like', $searchTerm)
                    ->orWhereHas('teacher', function ($teacherQuery) use ($searchTerm) {
                        $teacherQuery->where('name', 'like', $searchTerm);
                    });
            });
        }

        // Ambil hasil akhir dengan paginasi
        $assemblies = $query->simplePaginate($this->paginate);

        return view('livewire.list-majelis', [
            'assemblies_count' => $assemblies_count,
            'assemblies' => $assemblies
        ]);
    }
}
