<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Wirid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KontribusiAmalanController extends Controller
{
    public function create()
    {
        return view('pages.kontributor.amalan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'arab'      => 'required|string',
            'arti'      => 'nullable|string',
            'jumlah'    => 'required|integer|min:1',
            'waktu'     => 'nullable|string|max:100',
            'kategori'  => 'required|in:wirid,doa',
        ]);

        $validated['contributor_user_id'] = Auth::id();
        $validated['contribution_status'] = 'pending';

        $amalan = Wirid::create($validated);

        Contribution::create([
            'user_id'           => Auth::id(),
            'contributable_id'  => $amalan->id,
            'contributable_type'=> Wirid::class,
            'points_earned'     => 0,
        ]);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Amalan berhasil dikirim dan menunggu moderasi admin.');
    }

    public function edit(int $id)
    {
        $amalan = Wirid::where('contributor_user_id', Auth::id())->findOrFail($id);

        return view('pages.kontributor.amalan.edit', compact('amalan'));
    }

    public function update(Request $request, int $id)
    {
        $amalan = Wirid::where('contributor_user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'nama'      => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'arab'      => 'required|string',
            'arti'      => 'nullable|string',
            'jumlah'    => 'required|integer|min:1',
            'waktu'     => 'nullable|string|max:100',
            'kategori'  => 'required|in:wirid,doa',
        ]);

        if ($amalan->contribution_status === 'rejected') {
            $validated['contribution_status'] = 'pending';
            $validated['rejection_reason']    = null;
        }

        $amalan->update($validated);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Amalan berhasil diperbarui.');
    }
}
