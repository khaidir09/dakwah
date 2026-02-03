<?php

namespace App\Livewire;

use App\Models\Wirid;
use Livewire\Component;
use Livewire\WithPagination;

class ListWirid extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $kategori = 'wirid';

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setKategori($val)
    {
        $this->kategori = $val;
        $this->resetPage();
    }

    public function toggleLike($wiridId)
    {
        if (!auth()->check()) {
            session()->flash('message', 'Silakan login terlebih dahulu untuk menambahkan wirid ke favorit.');
            return redirect()->route('login');
        }

        $user = auth()->user();
        $wirid = Wirid::findOrFail($wiridId);

        if ($user->likedWirids()->where('wirid_id', $wiridId)->exists()) {
            $user->likedWirids()->detach($wiridId);
            $wirid->decrement('likes');
        } else {
            $user->likedWirids()->attach($wiridId);
            $wirid->increment('likes');
        }
    }

    public function render()
    {
        $wirids_count = Wirid::where('kategori', $this->kategori)->count();
        $query = Wirid::where('kategori', $this->kategori);

        // Jika user login, cek apakah dilike
        if (auth()->check()) {
            $query->withExists(['likedByUsers as is_liked' => function ($q) {
                $q->where('user_id', auth()->id());
            }]);
            $query->orderByDesc('is_liked');
        }

        $query->latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama', 'like', $searchTerm);
            });
        }

        // Ambil hasil akhir dengan paginasi
        $wirids = $query->simplePaginate($this->paginate);

        return view('livewire.list-wirid', [
            'wirids_count' => $wirids_count,
            'wirids' => $wirids
        ]);
    }
}
