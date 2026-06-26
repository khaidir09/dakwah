<?php

namespace App\Livewire;

use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class Guru extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $tab = 'semua';

    public $confirmingDeletion = false;
    public $teacher_id_to_delete;

    protected $updatesQueryString = ['search', 'tab'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($teacherId)
    {
        $this->teacher_id_to_delete = $teacherId; // Simpan ID
        $this->confirmingDeletion = true; // Buka modal
    }

    public function deleteTeacher()
    {
        // Pastikan ID ada
        if ($this->teacher_id_to_delete) {
            $teacher = Teacher::find($this->teacher_id_to_delete);

            if ($teacher) {
                $teacher->delete();
                // Kirim pesan sukses (akan kita tampilkan di view)
                session()->flash('message', 'Data guru berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->teacher_id_to_delete = null;
    }

    public function switchTab(string $tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $teachers_count = Teacher::count();
        $pending_count = Teacher::where('contribution_status', 'pending')->count();
        $query = Teacher::with('province', 'city', 'district', 'village')->latest();

        if ($this->tab === 'moderasi') {
            $query->where('contribution_status', 'pending');
        }

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', $searchTerm);
            });
        }

        $teachers = $query->simplePaginate($this->paginate);

        return view('livewire.guru', [
            'teachers_count' => $teachers_count,
            'pending_count' => $pending_count,
            'teachers' => $teachers,
        ]);
    }
}
