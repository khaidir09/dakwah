<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;

class Event extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    public $confirmingDeletion = false;
    public $event_id_to_delete;

    protected $updatesQueryString = ['search'];

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

    public function render()
    {
        $events_count = Event::count();
        $query = Event::with('province', 'city', 'district', 'village')->latest();

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', $searchTerm)
                         ->orWhere('location', 'like', $searchTerm)
                         ->orWhere('category', 'like', $searchTerm);
            });
        }

        // Ambil hasil akhir dengan paginasi
        $events = $query->simplePaginate($this->paginate);

        return view('livewire.event', [
            'events_count' => $events_count,
            'events' => $events
        ]);
    }
}
