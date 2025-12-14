<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Assembly;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Intervention\Image\Laravel\Facades\Image;

class MajelisController extends Controller
{
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
            'deskripsi' => 'required|string',
            'teacher_id' => 'required|exists:teachers,id',
            'alamat' => 'required|string',
            'maps' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'province' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = Str::uuid() . '.webp';

            // 1. Buat Versi THUMBNAIL (Untuk List/Avatar) - Crop Persegi
            $thumb = Image::read($file)
                ->cover(300, 300)
                ->toWebp(80);

            // 2. Buat Versi LARGE (Untuk Detail) - Resize Lebar, Tinggi menyesuaikan
            $large = Image::read($file)
                ->scale(width: 979)
                ->toWebp(80);

            // 3. Simpan ke Storage (Folder public)
            Storage::disk('public')->put('majelis/thumb/' . $filename, $thumb);
            Storage::disk('public')->put('majelis/large/' . $filename, $large);

            $validatedData['gambar'] = 'majelis/large/' . $filename;
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

        Assembly::create($validatedData);

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
            'deskripsi'    => 'required|string',
            'teacher_id'   => 'required|exists:teachers,id',
            'alamat'       => 'required|string',
            'maps'         => 'required|string|max:255',
            'gambar'       => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'province' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:20',
        ]);

        $majelis = Assembly::findOrFail($id);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = Str::uuid() . '.webp';

            // A. Proses Gambar Baru (Sama seperti store)
            $thumb = Image::read($file)
                ->cover(300, 300)
                ->toWebp(80);

            $large = Image::read($file)
                ->scale(width: 979)
                ->toWebp(80);

            // B. Simpan Gambar Baru
            Storage::disk('public')->put('majelis/thumb/' . $filename, (string) $thumb);
            Storage::disk('public')->put('majelis/large/' . $filename, (string) $large);

            // C. Hapus Gambar Lama (PENTING)
            if ($majelis->gambar) {
                // Hapus file 'large' (sesuai path di database)
                Storage::disk('public')->delete($majelis->gambar);

                // Hapus file 'thumb'. Kita perlu menebak path thumb berdasarkan path large.
                // Path DB: 'majelis/large/namafile.webp' -> Ubah 'large' jadi 'thumb'
                $oldThumbPath = str_replace('large', 'thumb', $majelis->gambar);

                if (Storage::disk('public')->exists($oldThumbPath)) {
                    Storage::disk('public')->delete($oldThumbPath);
                }
            }

            // D. Update array data dengan path baru
            $validatedData['gambar'] = 'majelis/large/' . $filename;
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
