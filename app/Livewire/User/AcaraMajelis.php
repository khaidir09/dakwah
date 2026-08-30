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
            // Wajib lewat query ter-scope: `event_id_to_delete` berasal dari klien,
            // sehingga `Event::find()` polos membuat siapa pun yang login dapat
            // menghapus acara milik majelis lain.
            $event = $this->ownedEvents()->find($this->event_id_to_delete);

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
        $events_count = $this->ownedEvents()->count();

        $events = $this->ownedEvents()
            ->with('assembly')
            ->orderBy('date', 'asc')
            ->simplePaginate($this->paginate);

        return view('livewire.user.acara-majelis', [
            'events_count' => $events_count,
            'events' => $events
        ]);
    }

    /**
     * Satu-satunya definisi "acara milik saya" di komponen ini: acara yang
     * majelisnya dimiliki pengguna yang sedang login. Dipakai untuk menampilkan
     * maupun menghapus, supaya keduanya tidak bisa lagi berbeda.
     */
    private function ownedEvents()
    {
        return Event::whereHas('assembly', function ($assemblyQuery) {
            $assemblyQuery->where('user_id', Auth::id());
        });
    }
}
