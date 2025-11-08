<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use Illuminate\Http\Request;

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
        return view('pages/majelis/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_majelis' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'guru' => 'required|string|max:255',
            'alamat' => 'required|string',
            'maps' => 'required|string|max:255',
            'gambar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $imagePath = $request->file('gambar')->store('majelis_images', 'public');
            $validatedData['gambar'] = $imagePath;
        }

        Assembly::create($validatedData);

        return redirect()->route('majelis.index')->with('status', 'Majelis berhasil ditambahkan!');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
