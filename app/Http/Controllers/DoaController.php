<?php

namespace App\Http\Controllers;

use App\Models\Doa;
use Illuminate\Http\Request;

class DoaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doa = Doa::all();
        return view('pages/doa/index', compact('doa'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages/doa/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'arab' => 'required|string',
            'arti' => 'nullable|string',
            'jumlah' => 'required|integer',
            'waktu' => 'nullable|string|max:100'
        ]);

        Doa::create($validatedData);

        return redirect()->route('doa.index')->with('message', 'Doa berhasil ditambahkan!');
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
        $doa = Doa::findOrFail($id);
        return view('pages.doa.edit', compact('doa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $doa = Doa::findOrFail($id);

        $validatedData = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'arab' => 'required|string',
            'arti' => 'nullable|string',
            'jumlah' => 'required|integer',
            'waktu' => 'nullable|string|max:100'
        ]);

        $doa->update($validatedData);

        return redirect()->route('doa.index')->with('message', 'Doa berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
