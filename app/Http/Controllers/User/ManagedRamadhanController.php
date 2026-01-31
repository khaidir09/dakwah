<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RamadhanSchedule;
use App\Models\Assembly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagedRamadhanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Find user's assembly
        $assembly = Assembly::where('user_id', Auth::id())->first();

        if (!$assembly) {
             return redirect()->route('kelola-majelis.create')->with('error', 'Silakan daftarkan majelis Anda terlebih dahulu.');
        }

        $schedules = RamadhanSchedule::where('assembly_id', $assembly->id)
            ->orderBy('hijri_year', 'desc')
            ->get();

        return view('pages.user.kelola-ramadhan.index', compact('schedules', 'assembly'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $assembly = Assembly::where('user_id', Auth::id())->firstOrFail();
        return view('pages.user.kelola-ramadhan.create', compact('assembly'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handled by Livewire
        return redirect()->route('kelola-ramadhan.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schedule = RamadhanSchedule::findOrFail($id);
        
        // Authorization
        if ($schedule->assembly->user_id !== Auth::id()) {
            abort(403);
        }

        return view('pages.user.kelola-ramadhan.edit', compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Handled by Livewire
        return redirect()->route('kelola-ramadhan.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = RamadhanSchedule::findOrFail($id);
        
        // Authorization
        if ($schedule->assembly->user_id !== Auth::id()) {
            abort(403);
        }

        $schedule->delete();

        return redirect()->route('kelola-ramadhan.index')->with('message', 'Jadwal berhasil dihapus.');
    }
}
