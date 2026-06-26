<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;

class Acara extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $tab = 'semua';

    public $confirmingDeletion = false;
    public $event_id_to_delete;

    protected $updatesQueryString = ['search', 'tab'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($eventId)
    {
        $this->event_id_to_delete = $eventId; // Simpan ID
        $this->confirmingDeletion = true; // Buka modal
    }

    public function deleteEvent()
    {
        // Pastikan ID ada
        if ($this->event_id_to_delete) {
            $event = Event::find($this->event_id_to_delete);

            if ($event) {
                $event->delete();
                // Kirim pesan sukses (akan kita tampilkan di view)
                session()->flash('message', 'Data event berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->event_id_to_delete = null;
    }

    public function switchTab(string $tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $events_count = Event::count();
        $pending_count = Event::where('status', 'pending')->whereNotNull('user_id')->count();
        $query = Event::with('province', 'city', 'district', 'village')->latest();

        if ($this->tab === 'moderasi') {
            $query->where('status', 'pending')->whereNotNull('user_id');
        }

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', $searchTerm)
                    ->orWhere('location', 'like', $searchTerm)
                    ->orWhere('category', 'like', $searchTerm);
            });
        }

        $events = $query->simplePaginate($this->paginate);

        return view('livewire.event', [
            'events_count' => $events_count,
            'pending_count' => $pending_count,
            'events' => $events,
        ]);
    }
}
