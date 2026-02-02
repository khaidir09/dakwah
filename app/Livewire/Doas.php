<?php

namespace App\Livewire;

use App\Models\Doa;
use Livewire\Component;
use Livewire\WithPagination;

class Doas extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $confirmingDeletion = false;
    public $doa_id_to_delete;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($doaId)
    {
        $this->doa_id_to_delete = $doaId; // Simpan ID
        $this->confirmingDeletion = true; // Buka modal
    }

    public function deleteDoa()
    {
        // Pastikan ID ada
        if ($this->doa_id_to_delete) {
            $doa = Doa::find($this->doa_id_to_delete);

            if ($doa) {
                $doa->delete();
                // Kirim pesan sukses (akan kita tampilkan di view)
                session()->flash('message', 'Data doa berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->doa_id_to_delete = null;
    }

    public function render()
    {
        $doas_count = Doa::count();
        $query = Doa::latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama', 'like', $searchTerm)->orWhere('waktu', 'like', $searchTerm);
            });
        }

        // Ambil hasil akhir dengan paginasi
        $doas = $query->simplePaginate($this->paginate);

        return view('livewire.doa', [
            'doas_count' => $doas_count,
            'doas' => $doas
        ]);
    }
}
