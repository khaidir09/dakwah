<?php

namespace App\Http\Controllers;

use App\Models\RamadhanSchedule;
use Illuminate\Http\Request;

class RamadhanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = RamadhanSchedule::orderBy('hijri_year', 'desc')->get();
        return view('pages.ramadhan.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.ramadhan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handled by Livewire
        return redirect()->route('ramadhan-schedules.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schedule = RamadhanSchedule::findOrFail($id);
        return view('pages.ramadhan.edit', compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Handled by Livewire
        return redirect()->route('ramadhan-schedules.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = RamadhanSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('ramadhan-schedules.index')->with('message', 'Jadwal berhasil dihapus.');
    }
}
