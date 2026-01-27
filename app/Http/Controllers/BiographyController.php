<?php

namespace App\Http\Controllers;

use App\Models\Biography;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class BiographyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.biographies.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.biographies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'deskripsi' => 'required|string',
            'maps' => 'nullable|string',
            'tanggal_wafat_masehi' => 'nullable|date',
            'tanggal_wafat_hijriah' => 'nullable|string',
        ]);

        $dataToCreate = $validatedData;
        $dataToCreate['slug'] = Str::slug($validatedData['nama']) . '-' . Str::random(6); // Ensure uniqueness simply

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = Str::uuid() . '.webp';

            // Resize/Crop 600x600 and convert to WebP
            $thumb = Image::read($file)
                ->cover(600, 600)
                ->toWebp(80);

            Storage::disk('public')->put('biographies/' . $filename, (string) $thumb);
            $dataToCreate['foto'] = 'biographies/' . $filename;
        }

        Biography::create($dataToCreate);

        return redirect()->route('biographies.index')->with('message', 'Manaqib berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Biography $biography)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Biography $biography)
    {
        return view('pages.biographies.edit', compact('biography'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Biography $biography)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'deskripsi' => 'required|string',
            'maps' => 'nullable|string',
            'tanggal_wafat_masehi' => 'nullable|date',
            'tanggal_wafat_hijriah' => 'nullable|string',
        ]);

        $dataToUpdate = $validatedData;
        if ($validatedData['nama'] !== $biography->nama) {
            $dataToUpdate['slug'] = Str::slug($validatedData['nama']) . '-' . Str::random(6);
        }

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = Str::uuid() . '.webp';

            $thumb = Image::read($file)
                ->cover(600, 600)
                ->toWebp(80);

            Storage::disk('public')->put('biographies/' . $filename, (string) $thumb);

            if ($biography->foto) {
                Storage::disk('public')->delete($biography->foto);
            }

            $dataToUpdate['foto'] = 'biographies/' . $filename;
        } else {
            unset($dataToUpdate['foto']);
        }

        $biography->update($dataToUpdate);

        return redirect()->route('biographies.index')->with('message', 'Manaqib berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Biography $biography)
    {
        if ($biography->foto) {
            Storage::disk('public')->delete($biography->foto);
        }

        $biography->delete();

        return redirect()->route('biographies.index')->with('message', 'Manaqib berhasil dihapus!');
    }
}
