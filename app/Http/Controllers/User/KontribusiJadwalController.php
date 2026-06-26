<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Contribution;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KontribusiJadwalController extends Controller
{
    public function create()
    {
        $majelisList = Assembly::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->whereNull('contribution_status')
                  ->orWhere('contribution_status', 'approved');
            })
            ->get();

        $teachers = Teacher::publiclyVisible()->where('wafat_masehi', null)->get();

        if ($majelisList->isEmpty()) {
            return redirect()->route('kontributor.saya')
                ->with('error', 'Anda belum memiliki Majelis yang disetujui. Tambahkan Majelis terlebih dahulu.');
        }

        return view('pages.kontributor.jadwal.create', compact('majelisList', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jadwal' => 'required|string|max:255',
            'assembly_id' => 'required|exists:assemblies,id',
            'teacher_id'  => 'required|exists:teachers,id',
            'waktu'       => 'required',
            'deskripsi'   => 'nullable|string',
            'hari'        => 'required|string|max:50',
            'access'      => 'required|string|in:Umum,Ikhwan,Akhwat',
        ]);

        $assembly = Assembly::where('id', $validated['assembly_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $assembly) {
            return back()->withErrors(['assembly_id' => 'Majelis tidak valid.']);
        }

        $validated['contributor_user_id'] = Auth::id();
        $validated['contribution_status'] = 'pending';

        $jadwal = Schedule::create($validated);

        Contribution::create([
            'user_id'           => Auth::id(),
            'contributable_id'  => $jadwal->id,
            'contributable_type'=> Schedule::class,
            'points_earned'     => 0,
        ]);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Jadwal berhasil dikirim dan menunggu moderasi admin.');
    }

    public function edit(int $id)
    {
        $jadwal = Schedule::where('contributor_user_id', Auth::id())->findOrFail($id);

        $majelisList = Assembly::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->whereNull('contribution_status')
                  ->orWhere('contribution_status', 'approved');
            })
            ->get();

        $teachers = Teacher::publiclyVisible()->where('wafat_masehi', null)->get();

        return view('pages.kontributor.jadwal.edit', compact('jadwal', 'majelisList', 'teachers'));
    }

    public function update(Request $request, int $id)
    {
        $jadwal = Schedule::where('contributor_user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'nama_jadwal' => 'required|string|max:255',
            'assembly_id' => 'required|exists:assemblies,id',
            'teacher_id'  => 'required|exists:teachers,id',
            'waktu'       => 'required',
            'deskripsi'   => 'nullable|string',
            'hari'        => 'required|string|max:50',
            'access'      => 'required|string|in:Umum,Ikhwan,Akhwat',
        ]);

        $assembly = Assembly::where('id', $validated['assembly_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $assembly) {
            return back()->withErrors(['assembly_id' => 'Majelis tidak valid.']);
        }

        if ($jadwal->contribution_status === 'rejected') {
            $validated['contribution_status'] = 'pending';
            $validated['rejection_reason']    = null;
        }

        $jadwal->update($validated);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }
}
