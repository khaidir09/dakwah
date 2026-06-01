<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Services\ImageService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Intervention\Image\Laravel\Facades\Image;

class GuruController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $guru = Teacher::all();
        return view('pages/guru/index', compact('guru'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
        return view('pages/guru/create', compact('provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'biografi' => 'required|string',
            'source' => 'nullable|array',
            'source.*.name' => 'required_with:source|string',
            'source.*.url' => 'nullable|url',
            'foto' => 'nullable|image|max:2048',
            'maps' => 'nullable|string',
            'tahun_lahir' => 'nullable|integer',
            'wafat_masehi' => 'nullable|date',
            'wafat_hijriah_day' => 'nullable|integer',
            'wafat_hijriah_month' => 'nullable|integer',
            'wafat_hijriah_year' => 'nullable|integer',
            'province' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:20',
        ]);

        $dataToCreate = $validatedData;

        if ($request->hasFile('foto')) {
            $dataToCreate['foto'] = $this->imageService->upload(
                $request->file('foto'),
                'guru',
                'cover',
                600,
                600
            );
        }

        $dataToCreate['province_code'] = $validatedData['province'] ?? null;
        $dataToCreate['city_code'] = $validatedData['city'] ?? null;
        $dataToCreate['district_code'] = $validatedData['district'] ?? null;
        $dataToCreate['village_code'] = $validatedData['village'] ?? null;

        // 5. Hapus key lama agar tidak error saat create
        unset(
            $dataToCreate['province'],
            $dataToCreate['city'],
            $dataToCreate['district'],
            $dataToCreate['village']
        );

        // Generate Slug
        $dataToCreate['slug'] = Teacher::generateSlug($dataToCreate['name']);

        // 6. Buat record baru di database
        Teacher::create($dataToCreate);

        return redirect()->route('guru.index')->with('message', 'Guru berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $guru = Teacher::findOrFail($id);
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
        return view('pages.guru.edit', compact('guru', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'biografi' => 'required|string',
            'source' => 'nullable|array',
            'source.*.name' => 'required_with:source|string',
            'source.*.url' => 'nullable|url',
            'foto' => 'nullable|image|max:2048',
            'maps' => 'nullable|string',
            'tahun_lahir' => 'nullable|integer',
            'wafat_masehi' => 'nullable|date',
            'wafat_hijriah_day' => 'nullable|integer',
            'wafat_hijriah_month' => 'nullable|integer',
            'wafat_hijriah_year' => 'nullable|integer',
            'province' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:20',
        ]);

        $guru = Teacher::findOrFail($id);

        $dataToUpdate = $validatedData;

        if ($request->hasFile('foto')) {
            $dataToUpdate['foto'] = $this->imageService->upload(
                $request->file('foto'),
                'guru',
                'cover',
                600,
                600,
                80,
                $guru->foto
            );
        } else {
            // Jika tidak ada file baru, hapus 'foto' dari array
            // agar tidak menimpa file yang ada dengan nilai null.
            unset($dataToUpdate['foto']);
        }

        // 5. Map data wilayah
        $dataToUpdate['province_code'] = $validatedData['province'] ?? null;
        $dataToUpdate['city_code'] = $validatedData['city'] ?? null;
        $dataToUpdate['district_code'] = $validatedData['district'] ?? null;
        $dataToUpdate['village_code'] = $validatedData['village'] ?? null;

        // 6. Hapus key form
        unset(
            $dataToUpdate['province'],
            $dataToUpdate['city'],
            $dataToUpdate['district'],
            $dataToUpdate['village']
        );

        // Generate Slug if name changed
        if ($guru->name !== $dataToUpdate['name']) {
            $dataToUpdate['slug'] = Teacher::generateSlug($dataToUpdate['name'], $id);
        }

        // 7. Update data guru
        $guru->update($dataToUpdate);

        return redirect()->route('guru.index')->with('message', 'Guru berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
