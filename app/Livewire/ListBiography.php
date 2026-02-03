<?php

namespace App\Livewire;

use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class ListBiography extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

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
        $query = Teacher::query();

        // Maybe default sorting by name or created_at?
        $query->latest();

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', $searchTerm)
                         ->orWhere('biografi', 'like', $searchTerm);
            });
        }

        $biographies = $query->simplePaginate($this->paginate);

        return view('livewire.list-biography', [
            'biographies_count' => $teachers_count,
            'biographies' => $biographies
        ]);
    }
}
