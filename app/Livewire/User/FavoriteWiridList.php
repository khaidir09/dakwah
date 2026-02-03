<?php

namespace App\Livewire\User;

use App\Models\Wirid;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class FavoriteWiridList extends Component
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
        $user = Auth::user();

        // Since we are in the favorites list, toggling implies removing it
        if ($user->likedWirids()->where('wirid_id', $wiridId)->exists()) {
            $user->likedWirids()->detach($wiridId);
            Wirid::find($wiridId)?->decrement('likes');
        }

        // We don't need to handle re-adding here because once removed, it's gone from the list.
        // The user can re-add it from the main library.
    }

    public function render()
    {
        $user = Auth::user();

        // Start query from the User's liked wirids relationship
        $query = $user->likedWirids()->where('kategori', $this->kategori);

        // Apply search filter if present
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama', 'like', $searchTerm)->orWhere('waktu', 'like', $searchTerm);
            });
        }

        // Order by pivot timestamp (recently added first) or standard
        // Since it's a pivot, we might want to order by when they liked it.
        // However, standard `latest()` on the Wirid model might be ambiguous.
        // Let's just order by ID desc for now (newest wirids) or if pivot has timestamps.
        // Checking existing User model: $this->belongsToMany(Wirid::class, 'wirid_user');
        // It doesn't explicitly say withTimestamps().

        // Let's just order by Wirid name for the library feel
        $query->orderBy('nama', 'asc');

        $wirids = $query->simplePaginate($this->paginate);

        return view('livewire.user.favorite-wirid-list', [
            'wirids' => $wirids
        ]);
    }
}
