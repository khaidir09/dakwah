<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Event;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AcaraMajelis extends Component
{
    use WithPagination;

    public $paginate = 10;

    public $confirmingDeletion = false;
    public $event_id_to_delete;

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
                session()->flash('message', 'Data acara majelis berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->event_id_to_delete = null;
    }

    public function render()
    {
        $events_count = Event::whereHas('assembly', function ($assemblyQuery) {
            $assemblyQuery->where('user_id', Auth::user()->id);
        })->count();
        $query = Event::with('assembly')
            ->whereHas('assembly', function ($assemblyQuery) {
                $assemblyQuery->where('user_id', Auth::user()->id);
            })->latest();

        // Ambil hasil akhir dengan paginasi
        $events = $query->simplePaginate($this->paginate);

        return view('livewire.user.acara-majelis', [
            'events_count' => $events_count,
            'events' => $events
        ]);
    }
}
