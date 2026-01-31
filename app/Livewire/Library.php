<?php

namespace App\Livewire;

use App\Models\Library as LibraryModel;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Library extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $confirmingDeletion = false;
    public $library_id_to_delete;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($libraryId)
    {
        $this->library_id_to_delete = $libraryId;
        $this->confirmingDeletion = true;
    }

    public function deleteLibrary()
    {
        if ($this->library_id_to_delete) {
            $library = LibraryModel::find($this->library_id_to_delete);

            if ($library) {
                if ($library->file_path && Storage::disk('public')->exists($library->file_path)) {
                    Storage::disk('public')->delete($library->file_path);
                }
                if ($library->cover_image && Storage::disk('public')->exists($library->cover_image)) {
                    Storage::disk('public')->delete($library->cover_image);
                }

                $library->delete();
                session()->flash('message', 'Data pustaka berhasil dihapus.');
            }
        }

        $this->confirmingDeletion = false;
        $this->library_id_to_delete = null;
    }

    public function render()
    {
        $libraries_count = LibraryModel::count();
        $query = LibraryModel::latest();

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where('title', 'like', $searchTerm)
                  ->orWhere('category', 'like', $searchTerm);
        }

        $libraries = $query->simplePaginate($this->paginate);

        return view('livewire.library', [
            'libraries_count' => $libraries_count,
            'libraries' => $libraries
        ]);
    }
}
