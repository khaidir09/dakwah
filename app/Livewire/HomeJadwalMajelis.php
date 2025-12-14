<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Schedule;
use Livewire\WithPagination;

class HomeJadwalMajelis extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $mapHari = [
            0 => 'Minggu',   // Carbon::SUNDAY
            1 => 'Senin',  // Carbon::MONDAY
            2 => 'Selasa', // Carbon::TUESDAY
            3 => 'Rabu',   // Carbon::WEDNESDAY
            4 => 'Kamis',  // Carbon::THURSDAY
            5 => 'Jumat',  // Carbon::FRIDAY
            6 => 'Sabtu',  // Carbon::SATURDAY
        ];
        // 3. Dapatkan hari ini sebagai angka (0 untuk Minggu, 1 untuk Senin, dst.)
        $hariIniAngka = Carbon::now()->dayOfWeek;

        // 4. Dapatkan nama hari yang sesuai dari array map
        $hariIni = $mapHari[$hariIniAngka];

        $schedules_count = Schedule::count();
        $query = Schedule::with('teacher', 'assembly')->where('hari', $hariIni);

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama_jadwal', 'like', $searchTerm)->orWhere('deskripsi', 'like', $searchTerm)->orWhere('hari', 'like', $searchTerm)
                    ->orWhereHas('teacher', function ($teacherQuery) use ($searchTerm) {
                        $teacherQuery->where('name', 'like', $searchTerm);
                    })->orWhereHas('assembly', function ($assemblyQuery) use ($searchTerm) {
                        $assemblyQuery->where('nama_majelis', 'like', $searchTerm);
                    });
            });
        }

        // Ambil hasil akhir dengan paginasi
        $schedules = $query->simplePaginate($this->paginate);

        return view('livewire.home-jadwal-majelis', [
            'schedules_count' => $schedules_count,
            'schedules' => $schedules
        ]);
    }
}
