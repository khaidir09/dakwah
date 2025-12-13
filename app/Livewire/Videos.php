<?php

namespace App\Livewire;

use App\Models\Video;
use Livewire\Component;
use Livewire\WithPagination;

class Videos extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $confirmingDeletion = false;
    public $video_id_to_delete;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($videoId)
    {
        $this->video_id_to_delete = $videoId; // Simpan ID
        $this->confirmingDeletion = true; // Buka modal
    }

    public function deleteVideo()
    {
        // Pastikan ID ada
        if ($this->video_id_to_delete) {
            $video = Video::find($this->video_id_to_delete);

            if ($video) {
                $video->delete();
                // Kirim pesan sukses (akan kita tampilkan di view)
                session()->flash('message', 'Data video berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->video_id_to_delete = null;
    }

    public function render()
    {
        $videos_count = Video::count();
        $query = Video::latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('title', 'like', $searchTerm)
                    ->orWhere('category', 'like', $searchTerm);
            });
        }

        // Ambil hasil akhir dengan paginasi
        $videos = $query->simplePaginate($this->paginate);

        return view('livewire.video', [
            'videos_count' => $videos_count,
            'videos' => $videos
        ]);
    }
}
