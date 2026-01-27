<?php

namespace App\Livewire;

use App\Models\Biography as BiographyModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Biography extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $confirmingDeletion = false;
    public $biography_id_to_delete;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($biographyId)
    {
        $this->biography_id_to_delete = $biographyId;
        $this->confirmingDeletion = true;
    }

    public function deleteBiography()
    {
        if ($this->biography_id_to_delete) {
            $biography = BiographyModel::find($this->biography_id_to_delete);

            if ($biography) {
                if ($biography->foto) {
                    Storage::disk('public')->delete($biography->foto);
                }

                $biography->delete();
                session()->flash('message', 'Data biografi berhasil dihapus.');
            }
        }

        $this->confirmingDeletion = false;
        $this->biography_id_to_delete = null;
    }

    public function render()
    {
        $biographies_count = BiographyModel::count();
        $query = BiographyModel::latest();

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where('nama', 'like', $searchTerm);
        }

        $biographies = $query->simplePaginate($this->paginate);

        return view('livewire.biography', [
            'biographies_count' => $biographies_count,
            'biographies' => $biographies
        ]);
    }
}
