<?php

namespace App\Livewire;

use App\Models\Schedule;
use Livewire\Component;
use Livewire\WithPagination;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class ListJadwalMajelis extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;
    public $access = '';

    // Filter Properties
    public $selectedType = null;
    public $selectedProvince = null;
    public $selectedCity = null;
    public $selectedDistrict = null;

    protected $updatesQueryString = ['search', 'access'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->access = request()->query('access', $this->access);

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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAccess()
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
        $schedules_count = Schedule::count();
        $query = Schedule::with('teacher', 'assembly')->orderByRaw("
            CASE hari
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                WHEN 'Minggu' THEN 7
                ELSE 8
            END
        ");

        if ($this->access) {
            $query->where('access', $this->access);
        }

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

        return view('livewire.list-jadwal-majelis', [
            'schedules_count' => $schedules_count,
            'schedules' => $schedules,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'types' => ['Majelis', 'Mesjid', 'Langgar', 'Musholla'],
        ]);
    }
}
