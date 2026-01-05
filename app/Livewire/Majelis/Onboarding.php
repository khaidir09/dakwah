<?php

namespace App\Livewire\Majelis;

use App\Models\Teacher;
use App\Models\Assembly;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;
use Intervention\Image\Laravel\Facades\Image;

class Onboarding extends Component
{
    use WithFileUploads;

    public $step = 1;

    // Step 1: Search Teacher
    public $searchKeyword = '';
    public $selectedTeacherId = null;
    public $selectedTeacherName = '';

    // Step 2: Create Teacher (if needed)
    public $teacherName;
    public $teacherBio;
    // public $teacherDomisili; // Removed as requested
    public $teacherPhoto;
    public $teacherBirthYear; // Added

    // Teacher Region Data
    public $teacherProvinces = []; // Should be same as global provinces really, but keep separate if logic needs it
    public $teacherCities = [];
    public $teacherDistricts = [];
    public $teacherVillages = [];

    public $selectedTeacherProvince = null;
    public $selectedTeacherCity = null;
    public $selectedTeacherDistrict = null;
    public $selectedTeacherVillage = null;

    // Step 3: Create Majelis
    public $majelisName;
    public $majelisDesc;
    public $majelisAddress;
    public $majelisMaps;
    public $majelisImage;

    // Majelis Region Data
    public $provinces = [];
    public $cities = [];
    public $districts = [];
    public $villages = [];

    public $selectedProvince = null;
    public $selectedCity = null;
    public $selectedDistrict = null;
    public $selectedVillage = null;

    public function mount()
    {
        $this->provinces = Province::pluck('name', 'code');
        // Teacher uses same source
        $this->teacherProvinces = $this->provinces;
    }

    // --- Step 1 Logic ---

    public function updatedSearchKeyword()
    {
        $this->reset('selectedTeacherId');
    }

    public function selectTeacher($id, $name)
    {
        $this->selectedTeacherId = $id;
        $this->selectedTeacherName = $name;
    }

    public function proceedToStep3WithTeacher()
    {
        $this->validate([
            'selectedTeacherId' => 'required|exists:teachers,id'
        ]);
        $this->step = 3;
    }

    public function goToStep2()
    {
        $this->step = 2;
    }

    // --- Step 2 Logic ---

    // Dependent Dropdowns for Teacher
    public function updatedSelectedTeacherProvince($value)
    {
        $this->teacherCities = City::where('province_code', $value)->pluck('name', 'code');
        $this->selectedTeacherCity = null;
        $this->selectedTeacherDistrict = null;
        $this->selectedTeacherVillage = null;
    }

    public function updatedSelectedTeacherCity($value)
    {
        $this->teacherDistricts = District::where('city_code', $value)->pluck('name', 'code');
        $this->selectedTeacherDistrict = null;
        $this->selectedTeacherVillage = null;
    }

    public function updatedSelectedTeacherDistrict($value)
    {
        $this->teacherVillages = Village::where('district_code', $value)->pluck('name', 'code');
        $this->selectedTeacherVillage = null;
    }

    public function saveTeacherAndProceed()
    {
        $this->validate([
            'teacherName' => 'required|string|max:100',
            'teacherBio' => 'required|string',
            'teacherPhoto' => 'required|image|max:2048', // 2MB Max
            'selectedTeacherProvince' => 'required',
            'selectedTeacherCity' => 'required',
            'selectedTeacherDistrict' => 'required',
            'selectedTeacherVillage' => 'required',
            'teacherBirthYear' => 'nullable|integer|digits:4',
        ]);

        // Upload Photo
        $photoPath = $this->teacherPhoto->store('public/teachers');

        // Resolve Domisili Name from City Code
        $city = City::where('code', $this->selectedTeacherCity)->first();
        $domisiliName = $city ? $city->name : '-';

        $teacher = Teacher::create([
            'name' => $this->teacherName,
            'biografi' => $this->teacherBio,
            'domisili' => $domisiliName, // Fill legacy column
            'foto' => $photoPath,
            'tahun_lahir' => $this->teacherBirthYear,

            // New Region Codes
            'province_code' => $this->selectedTeacherProvince,
            'city_code' => $this->selectedTeacherCity,
            'district_code' => $this->selectedTeacherDistrict,
            'village_code' => $this->selectedTeacherVillage,

            'wafat_masehi' => null,
            'wafat_hijriah' => null,
        ]);

        $this->selectedTeacherId = $teacher->id;
        $this->selectedTeacherName = $teacher->name;
        $this->step = 3;
    }

    public function backToStep1()
    {
        $this->step = 1;
    }

    // --- Step 3 Logic ---

    public function updatedSelectedProvince($value)
    {
        $this->cities = City::where('province_code', $value)->pluck('name', 'code');
        $this->selectedCity = null;
        $this->selectedDistrict = null;
        $this->selectedVillage = null;
    }

    public function updatedSelectedCity($value)
    {
        $this->districts = District::where('city_code', $value)->pluck('name', 'code');
        $this->selectedDistrict = null;
        $this->selectedVillage = null;
    }

    public function updatedSelectedDistrict($value)
    {
        $this->villages = Village::where('district_code', $value)->pluck('name', 'code');
        $this->selectedVillage = null;
    }

    public function saveMajelis()
    {
        $this->validate([
            'selectedTeacherId' => 'required|exists:teachers,id',
            'majelisName' => 'required|string|max:255',
            'majelisDesc' => 'required|string',
            'majelisAddress' => 'required|string',
            'majelisMaps' => 'required|string|max:255',
            'majelisImage' => 'nullable|image|max:2048',
            'selectedProvince' => 'required',
            'selectedCity' => 'required',
            'selectedDistrict' => 'required',
            'selectedVillage' => 'required',
        ]);

        $imagePath = null;
        if ($this->majelisImage) {
            $file = $this->majelisImage;
            $filename = time() . '.' . $file->getClientOriginalExtension();

            $pathLarge = 'public/majelis/large/' . $filename;
            $pathThumb = 'public/majelis/thumb/' . $filename;

            $image = Image::read($file->getRealPath());
            $image->scaleDown(width: 800);
            Storage::put($pathLarge, $image->toJpeg(80));

            $imageThumb = Image::read($file->getRealPath());
            $imageThumb->scaleDown(width: 400);
            Storage::put($pathThumb, $imageThumb->toJpeg(80));

            $imagePath = $pathLarge;
        }

        Assembly::create([
            'user_id' => Auth::id(),
            'teacher_id' => $this->selectedTeacherId,
            'nama_majelis' => $this->majelisName,
            'deskripsi' => $this->majelisDesc,
            'alamat' => $this->majelisAddress,
            'maps' => $this->majelisMaps,
            'gambar' => $imagePath,
            'status' => 'Aktif',
            'guru' => $this->selectedTeacherName,

            // Region Codes
            'province_code' => $this->selectedProvince,
            'city_code' => $this->selectedCity,
            'district_code' => $this->selectedDistrict,
            'village_code' => $this->selectedVillage,
        ]);

        return redirect()->route('kelola-majelis.index')->with('status', 'Majelis berhasil dibuat! Silakan kelola jadwal Anda.');
    }

    public function render()
    {
        $teachers = [];
        if ($this->step == 1 && strlen($this->searchKeyword) >= 2) {
            $teachers = Teacher::where('name', 'like', '%' . $this->searchKeyword . '%')
                               ->take(10)
                               ->get();
        }

        return view('livewire.majelis.onboarding', [
            'teachers' => $teachers
        ]);
    }
}
