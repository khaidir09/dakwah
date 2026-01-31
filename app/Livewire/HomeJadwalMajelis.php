<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Schedule;
use Livewire\WithPagination;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class HomeJadwalMajelis extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    // Filter Properties
    public $selectedType = null;
    public $selectedProvince = null;
    public $selectedCity = null;
    public $selectedDistrict = null;

    protected $updatesQueryString = ['search'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->province_code) {
                $this->selectedProvince = $user->province_code;

                if ($user->city_code) {
                    $this->selectedCity = $user->city_code;
                }
            }
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedType($value)
    {
        $this->resetPage();
    }

    public function updatedSelectedProvince($value)
    {
        $this->selectedCity = null;
        $this->selectedDistrict = null;
        $this->resetPage();
    }

    public function updatedSelectedCity($value)
    {
        $this->selectedDistrict = null;
        $this->resetPage();
    }

    public function updatedSelectedDistrict($value)
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

        // Prepare Query
        $query = Schedule::with('teacher', 'assembly')->where('hari', $hariIni);

        // Apply Filters
        if ($this->selectedType) {
            $query->whereHas('assembly', function ($q) {
                $q->where('tipe', $this->selectedType);
            });
        }

        // Apply Region Filters
        if ($this->selectedProvince) {
            $query->whereHas('assembly', function ($q) {
                $q->where('province_code', $this->selectedProvince);
            });
        }

        if ($this->selectedCity) {
            $query->whereHas('assembly', function ($q) {
                $q->where('city_code', $this->selectedCity);
            });
        }

        if ($this->selectedDistrict) {
            $query->whereHas('assembly', function ($q) {
                $q->where('district_code', $this->selectedDistrict);
            });
        }

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

        $schedules_count = $query->count(); // Count filtered results

        // Ambil hasil akhir dengan paginasi
        $schedules = $query->simplePaginate($this->paginate);

        // Fetch Data for Dropdowns
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');

        $cities = $this->selectedProvince
            ? City::where('province_code', $this->selectedProvince)->pluck('name', 'code')
            : [];

        $districts = $this->selectedCity
            ? District::where('city_code', $this->selectedCity)->pluck('name', 'code')
            : [];

        return view('livewire.home-jadwal-majelis', [
            'schedules_count' => $schedules_count,
            'schedules' => $schedules,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'types' => ['Majelis', 'Mesjid', 'Langgar', 'Musholla'],
        ]);
    }
}
