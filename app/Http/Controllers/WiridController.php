<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Wirid;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Intervention\Image\Laravel\Facades\Image;

class WiridController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wirid = Wirid::all();
        return view('pages/wirid/index', compact('wirid'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages/wirid/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'arab' => 'required|string',
            'arti' => 'nullable|string',
            'jumlah' => 'required|integer',
            'waktu' => 'nullable|string|max:100'
        ]);

        Wirid::create($validatedData);

        return redirect()->route('wirid.index')->with('message', 'Wirid berhasil ditambahkan!');
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
        $wirid = Wirid::findOrFail($id);
        return view('pages.wirid.edit', compact('wirid'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $wirid = Wirid::findOrFail($id);

        $validatedData = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'arab' => 'required|string',
            'arti' => 'nullable|string',
            'jumlah' => 'required|integer',
            'waktu' => 'nullable|string|max:100'
        ]);

        $wirid->update($validatedData);

        return redirect()->route('wirid.index')->with('message', 'Wirid berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
