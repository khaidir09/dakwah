<?php

namespace App\Livewire;

use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class ListGuru extends Component
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
        $teachers_count = Teacher::count();
        $query = Teacher::latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', $searchTerm);
            });
        }

        // Ambil hasil akhir dengan paginasi
        $teachers = $query->simplePaginate($this->paginate);

        return view('livewire.list-guru', [
            'teachers_count' => $teachers_count,
            'teachers' => $teachers
        ]);
    }
}
