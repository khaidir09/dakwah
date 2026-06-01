<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Assembly;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Intervention\Image\Laravel\Facades\Image;
use App\Traits\HandlesImageUploads;

class MajelisController extends Controller
{
    use HandlesImageUploads;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $majelis = Assembly::all();
        return view('pages/majelis/index', compact('majelis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teachers = Teacher::where('wafat_masehi', null)->get();
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
        return view('pages/majelis/create', compact('teachers', 'provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_majelis' => 'required|string|max:255',
            'tipe' => 'nullable|string|in:Majelis,Mesjid,Langgar,Musholla',
            'deskripsi' => 'required|string',
            'teacher_id' => 'nullable|required_without:custom_leader_name|exists:teachers,id',
            'custom_leader_name' => 'nullable|required_without:teacher_id|string|max:255',
            'alamat' => 'required|string',
            'maps' => 'nullable|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'province' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:20',
        ]);

        // Logic to ensure only one is set
        if (!empty($validatedData['teacher_id'])) {
            $validatedData['custom_leader_name'] = null;
        } else {
            $validatedData['teacher_id'] = null;
        }

        if ($request->hasFile('gambar')) {
            $paths = $this->uploadImageWithThumbnail($request->file('gambar'), 'majelis');
            $validatedData['gambar'] = $paths['large'];
        } else {
            unset($validatedData['gambar']);
        }

        $validatedData['province_code'] = $validatedData['province'] ?? null;
        $validatedData['city_code'] = $validatedData['city'] ?? null;
        $validatedData['district_code'] = $validatedData['district'] ?? null;
        $validatedData['village_code'] = $validatedData['village'] ?? null;

        unset(
            $validatedData['province'],
            $validatedData['city'],
            $validatedData['district'],
            $validatedData['village']
        );

        $assembly = new Assembly($validatedData);
        $assembly->save();

        return redirect()->route('majelis.index')->with('message', 'Majelis berhasil ditambahkan!');
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
        $majelis = Assembly::findOrFail($id);
        $teachers = Teacher::where('wafat_masehi', null)->get();
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
        return view('pages.majelis.edit', compact('majelis', 'teachers', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'nama_majelis' => 'required|string|max:255',
            'tipe' => 'nullable|string|in:Majelis,Mesjid,Langgar,Musholla',
            'deskripsi'    => 'required|string',
            'teacher_id'   => 'nullable|required_without:custom_leader_name|exists:teachers,id',
            'custom_leader_name' => 'nullable|required_without:teacher_id|string|max:255',
            'alamat'       => 'required|string',
            'maps'         => 'required|string|max:255',
            'gambar'       => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'province' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:20',
        ]);

        // Logic to ensure only one is set
        if (!empty($validatedData['teacher_id'])) {
            $validatedData['custom_leader_name'] = null;
        } else {
            $validatedData['teacher_id'] = null;
        }

        $majelis = Assembly::findOrFail($id);

        if ($request->hasFile('gambar')) {
            // A. Proses dan Simpan Gambar Baru
            $paths = $this->uploadImageWithThumbnail($request->file('gambar'), 'majelis');

            // B. Hapus Gambar Lama (PENTING)
            if ($majelis->gambar) {
                $this->deleteImageWithThumbnail($majelis->gambar);
            }

            // C. Update array data dengan path baru
            $validatedData['gambar'] = $paths['large'];
        } else {
            // Jika tidak upload gambar baru, hapus key 'gambar' dari array
            // supaya data gambar lama di database tidak tertimpa null
            unset($validatedData['gambar']);
        }

        // 4. Update Database
        $validatedData['province_code'] = $validatedData['province'] ?? null;
        $validatedData['city_code'] = $validatedData['city'] ?? null;
        $validatedData['district_code'] = $validatedData['district'] ?? null;
        $validatedData['village_code'] = $validatedData['village'] ?? null;

        unset(
            $validatedData['province'],
            $validatedData['city'],
            $validatedData['district'],
            $validatedData['village']
        );

        $majelis->update($validatedData);

        return redirect()->route('majelis.index')->with('message', 'Majelis berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
