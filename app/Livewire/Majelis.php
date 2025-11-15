<?php

namespace App\Livewire;

use App\Models\Assembly;
use Livewire\Component;
use Livewire\WithPagination;

class Majelis extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $confirmingDeletion = false;
    public $assembly_id_to_delete;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($assemblyId)
    {
        $this->assembly_id_to_delete = $assemblyId; // Simpan ID
        $this->confirmingDeletion = true; // Buka modal
    }

    public function deleteAssembly()
    {
        // Pastikan ID ada
        if ($this->assembly_id_to_delete) {
            $assembly = Assembly::find($this->assembly_id_to_delete);

            if ($assembly) {
                $assembly->delete();
                // Kirim pesan sukses (akan kita tampilkan di view)
                session()->flash('message', 'Data majelis berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->assembly_id_to_delete = null;
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

        return view('livewire.majelis', [
            'assemblies_count' => $assemblies_count,
            'assemblies' => $assemblies
        ]);
    }
}
