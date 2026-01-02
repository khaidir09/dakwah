<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Schedule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class JadwalMajelis extends Component
{
    use WithPagination;

    public $paginate = 10;

    public $confirmingDeletion = false;
    public $schedule_id_to_delete;

    public function confirmDelete($scheduleId)
    {
        $this->schedule_id_to_delete = $scheduleId; // Simpan ID
        $this->confirmingDeletion = true; // Buka modal
    }

    public function deleteSchedule()
    {
        // Pastikan ID ada
        if ($this->schedule_id_to_delete) {
            $schedule = Schedule::find($this->schedule_id_to_delete);

            if ($schedule) {
                $schedule->delete();
                // Kirim pesan sukses (akan kita tampilkan di view)
                session()->flash('message', 'Data jadwal majelis berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->schedule_id_to_delete = null;
    }

    public function render()
    {
        $schedules_count = Schedule::whereHas('assembly', function ($assemblyQuery) {
            $assemblyQuery->where('user_id', Auth::user()->id);
        })->count();
        $query = Schedule::with(['teacher', 'assembly'])
            ->whereHas('assembly', function ($assemblyQuery) {
                $assemblyQuery->where('user_id', Auth::user()->id);
            })
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");

        // Ambil hasil akhir dengan paginasi
        $schedules = $query->simplePaginate($this->paginate);

        return view('livewire.user.jadwal-majelis', [
            'schedules_count' => $schedules_count,
            'schedules' => $schedules
        ]);
    }
}
