<?php

namespace App\Livewire;

use App\Models\Wirid;
use Livewire\Component;
use Livewire\WithPagination;

class Wirids extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $confirmingDeletion = false;
    public $wirid_id_to_delete;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($wiridId)
    {
        $this->wirid_id_to_delete = $wiridId; // Simpan ID
        $this->confirmingDeletion = true; // Buka modal
    }

    public function deleteWirid()
    {
        // Pastikan ID ada
        if ($this->wirid_id_to_delete) {
            $wirid = Wirid::find($this->wirid_id_to_delete);

            if ($wirid) {
                $wirid->delete();
                // Kirim pesan sukses (akan kita tampilkan di view)
                session()->flash('message', 'Data wirid berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->wirid_id_to_delete = null;
    }

    public function render()
    {
        $wirids_count = Wirid::count();
        $query = Wirid::latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama', 'like', $searchTerm)->orWhere('waktu', 'like', $searchTerm);
            });
        }

        // Ambil hasil akhir dengan paginasi
        $wirids = $query->simplePaginate($this->paginate);

        return view('livewire.wirid', [
            'wirids_count' => $wirids_count,
            'wirids' => $wirids
        ]);
    }
}
