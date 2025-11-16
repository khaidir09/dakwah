<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\Province;

class GuruController extends Controller
{
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
        $provinces = Province::pluck('name', 'code');
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
            'foto' => 'nullable|image|max:2048',
            'domisili' => 'required|string|max:255',
            'tahun_lahir' => 'nullable|integer',
            'wafat_masehi' => 'nullable|integer',
            'wafat_hijriah_day' => 'nullable|integer',
            'wafat_hijriah_month' => 'nullable|integer',
            'wafat_hijriah_year' => 'nullable|integer',
        ]);

        if ($request->hasFile('foto')) {
            $imagePath = $request->file('foto')->store('teacher_photos', 'public');
            $validatedData['foto'] = $imagePath;
        }

        Teacher::create($validatedData);

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
        return view('pages.guru.edit', compact('guru'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'nama_guru' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'guru' => 'required|string|max:255',
            'alamat' => 'required|string',
            'maps' => 'required|string|max:255',
            'gambar' => 'nullable|image|max:2048',
        ]);

        $guru = Teacher::findOrFail($id);

        if ($request->hasFile('gambar')) {
            $imagePath = $request->file('gambar')->store('guru_images', 'public');
            $validatedData['gambar'] = $imagePath;
        }

        $guru->update($validatedData);

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
