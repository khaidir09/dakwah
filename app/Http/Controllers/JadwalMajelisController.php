<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Assembly;
use App\Models\Schedule;
use Illuminate\Http\Request;

class JadwalMajelisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jadwal = Schedule::with('assembly')->get();
        return view('pages.jadwal-majelis.index', compact('jadwal'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $majelis = Assembly::all();
        $teachers = Teacher::where('wafat_masehi', null)->get();
        return view('pages.jadwal-majelis.create', compact('majelis', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_jadwal' => 'required|string|max:255',
            'assembly_id' => 'required|exists:assemblies,id',
            'teacher_id' => 'required|exists:teachers,id',
            'waktu' => 'required',
            'deskripsi' => 'string|nullable',
            'hari' => 'required|string|max:50',
            'access' => 'required|string|in:Umum,Ikhwan,Akhwat',
        ]);

        Schedule::create($validatedData);

        return redirect()->route('jadwal-majelis.index')->with('message', 'Jadwal majelis berhasil ditambahkan!');
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
        $jadwal = Schedule::with('assembly')->findOrFail($id);
        $majelis = Assembly::all();
        $teachers = Teacher::where('wafat_masehi', null)->get();
        return view('pages.jadwal-majelis.edit', compact('jadwal', 'majelis', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'nama_jadwal' => 'required|string|max:255',
            'assembly_id' => 'required|exists:assemblies,id',
            'teacher_id' => 'required|exists:teachers,id',
            'deskripsi' => 'string|nullable',
            'waktu' => 'required',
            'hari' => 'required',
            'access' => 'required|string|in:Umum,Ikhwan,Akhwat',
        ]);

        $jadwal = Schedule::findOrFail($id);
        $jadwal->update($validatedData);

        return redirect()->route('jadwal-majelis.index')->with('message', 'Jadwal majelis berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jadwal = Schedule::findOrFail($id);
        $jadwal->delete();

        return redirect()->route('jadwal-majelis.index')->with('status', 'Jadwal majelis berhasil dihapus!');
    }
}
