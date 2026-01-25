<?php

namespace App\Livewire\User;

use App\Models\Teacher;
use Livewire\Component;
use App\Models\Assembly;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Laravolt\Indonesia\Models\City;
use Illuminate\Support\Facades\Auth;
use Laravolt\Indonesia\Models\Village;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
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
    public $teacherPhoto;
    public $teacherBirthYear;

    // Teacher Region Data
    public $teacherProvinces = [];
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
    public $gambar;

    // Social Media
    public $youtube;
    public $instagram;
    public $facebook;
    public $tiktok;

    // Region Data
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
        if (Assembly::where('user_id', Auth::id())->exists()) {
            return redirect()->route('kelola-jadwal-majelis')->with('error', 'Anda sudah memiliki majelis terdaftar.');
        }

        $this->provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
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

        $photoPath = null;
        if ($this->teacherPhoto) {
            // Process Photo (Match GuruController logic)
            $file = $this->teacherPhoto;
            $filename = Str::uuid() . '.webp';

            $thumb = Image::read($file->getRealPath())
                ->cover(600, 600)
                ->toWebp(80);

            // Upload Photo
            Storage::disk('public')->put('guru/' . $filename, $thumb);

            $photoPath = 'guru/' . $filename;
        }

        $teacher = Teacher::create([
            'name' => $this->teacherName,
            'biografi' => $this->teacherBio,
            'foto' => $photoPath, // Full path
            // Nullables
            'tahun_lahir' => $this->teacherBirthYear,

            // New Region Codes
            'province_code' => $this->selectedTeacherProvince,
            'city_code' => $this->selectedTeacherCity,
            'district_code' => $this->selectedTeacherDistrict,
            'village_code' => $this->selectedTeacherVillage,
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
            'gambar' => 'nullable|image|max:2048',
            'youtube' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'selectedProvince' => 'required',
            'selectedCity' => 'required',
            'selectedDistrict' => 'required',
            'selectedVillage' => 'required',
        ]);

        // Replicating logic from ManagedMajelisController
        $file = $this->gambar;
        $filename = Str::uuid() . '.webp';

        // Paths
        $pathLarge = 'public/majelis/large/' . $filename;
        $pathThumb = 'public/majelis/thumb/' . $filename;

        // Processing
        // Note: In Livewire temporary upload, $file is a TemporaryUploadedFile wrapper.
        // We need the real path for Intervention Image.

        $image = Image::read($file->getRealPath());
        $image->scaleDown(width: 800);
        Storage::put($pathLarge, $image->toWebp(80));

        $imageThumb = Image::read($file->getRealPath());
        $imageThumb->scaleDown(width: 400);
        Storage::put($pathThumb, $imageThumb->toWebp(80));

        $imagePath = $pathLarge;

        Assembly::create([
            'user_id' => Auth::id(),
            'teacher_id' => $this->selectedTeacherId,
            'nama_majelis' => $this->majelisName,
            'deskripsi' => $this->majelisDesc,
            'alamat' => $this->majelisAddress,
            'maps' => $this->majelisMaps,
            'gambar' => $imagePath,
            'status' => 'Aktif',

            // Social Media
            'youtube' => $this->youtube,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'tiktok' => $this->tiktok,

            // Region Codes
            'province_code' => $this->selectedProvince,
            'city_code' => $this->selectedCity,
            'district_code' => $this->selectedDistrict,
            'village_code' => $this->selectedVillage,
        ]);

        return redirect()->route('kelola-jadwal-majelis')->with('status', 'Majelis berhasil dibuat! Silakan kelola jadwal Anda.');
    }

    public function render()
    {
        $teachers = [];
        if ($this->step == 1 && strlen($this->searchKeyword) >= 2) {
            $teachers = Teacher::where('name', 'like', '%' . $this->searchKeyword . '%')
                ->take(10)
                ->get();
        }

        return view('livewire.user.onboarding', [
            'teachers' => $teachers
        ]);
    }
}
