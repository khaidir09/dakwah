<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Assembly;
use Livewire\WithPagination;
use Laravolt\Indonesia\Models\City;
use Illuminate\Support\Facades\Auth;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class ListMajelis extends Component
{
    use WithPagination;

    public $paginate = 10;
    public $search;

    // Filter Properties
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

                    if ($user->district_code) {
                        $this->selectedDistrict = $user->district_code;
                    }
                }
            }
        }
    }

    public function updatingSearch()
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
        $query = Assembly::with('teacher');

        if (auth()->check()) {
            $query->withExists(['followers as is_followed' => function ($q) {
                $q->where('user_id', Auth::user()->id);
            }]);
            $query->orderByDesc('is_followed');
        }

        $query->latest();

        // Apply Region Filters
        if ($this->selectedProvince) {
            $query->where('province_code', $this->selectedProvince);
        }

        if ($this->selectedCity) {
            $query->where('city_code', $this->selectedCity);
        }

        if ($this->selectedDistrict) {
            $query->where('district_code', $this->selectedDistrict);
        }

        // Jika ada pencarian, tambahkan kondisi where
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('nama_majelis', 'like', $searchTerm)
                    ->orWhereHas('teacher', function ($teacherQuery) use ($searchTerm) {
                        $teacherQuery->where('name', 'like', $searchTerm);
                    });
            });
        }

        $assemblies_count = $query->count(); // Count filtered results

        // Ambil hasil akhir dengan paginasi
        $assemblies = $query->simplePaginate($this->paginate);

        // Fetch Data for Dropdowns
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');

        $cities = $this->selectedProvince
            ? City::where('province_code', $this->selectedProvince)->pluck('name', 'code')
            : [];

        $districts = $this->selectedCity
            ? District::where('city_code', $this->selectedCity)->pluck('name', 'code')
            : [];

        return view('livewire.list-majelis', [
            'assemblies_count' => $assemblies_count,
            'assemblies' => $assemblies,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
        ]);
    }
}
