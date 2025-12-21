<?php

namespace App\Livewire;

use App\Models\Schedule;
use Livewire\Component;
use Livewire\WithPagination;

class ListJadwalMajelis extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $access = '';

    protected $updatesQueryString = ['search', 'access'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->access = request()->query('access', $this->access);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAccess()
    {
        $this->resetPage();
    }

    public function render()
    {
        $schedules_count = Schedule::count();
        $query = Schedule::with('teacher', 'assembly')->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");

        if ($this->access) {
            $query->where('access', $this->access);
        }

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama_jadwal', 'like', $searchTerm)->orWhere('deskripsi', 'like', $searchTerm)->orWhere('hari', 'like', $searchTerm)
                    ->orWhereHas('teacher', function ($teacherQuery) use ($searchTerm) {
                        $teacherQuery->where('name', 'like', $searchTerm);
                    })->orWhereHas('assembly', function ($assemblyQuery) use ($searchTerm) {
                        $assemblyQuery->where('nama_majelis', 'like', $searchTerm);
                    });
            });
        }

        // Ambil hasil akhir dengan paginasi
        $schedules = $query->simplePaginate($this->paginate);

        return view('livewire.list-jadwal-majelis', [
            'schedules_count' => $schedules_count,
            'schedules' => $schedules
        ]);
    }
}
