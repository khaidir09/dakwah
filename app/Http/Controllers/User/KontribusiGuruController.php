<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Teacher;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravolt\Indonesia\Models\Province;

class KontribusiGuruController extends Controller
{
    public function __construct(protected ImageService $imageService) {}

    public function create()
    {
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');

        return view('pages.kontributor.guru.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'biografi'    => 'required|string',
            'foto'        => 'nullable|image|max:2048',
            'maps'        => 'nullable|string',
            'tahun_lahir' => 'nullable|integer',
            'province'    => 'nullable|string|max:20',
            'city'        => 'nullable|string|max:20',
            'district'    => 'nullable|string|max:20',
            'village'     => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->imageService->upload($request->file('foto'), 'guru', 'cover', 600, 600);
        }

        $validated['province_code'] = $validated['province'] ?? null;
        $validated['city_code']     = $validated['city'] ?? null;
        $validated['district_code'] = $validated['district'] ?? null;
        $validated['village_code']  = $validated['village'] ?? null;
        unset($validated['province'], $validated['city'], $validated['district'], $validated['village']);

        $validated['slug']                = Teacher::generateSlug($validated['name']);
        $validated['contributor_user_id'] = Auth::id();
        $validated['contribution_status'] = 'pending';

        $guru = Teacher::create($validated);

        Contribution::create([
            'user_id'           => Auth::id(),
            'contributable_id'  => $guru->id,
            'contributable_type'=> Teacher::class,
            'points_earned'     => 0,
        ]);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Data guru berhasil dikirim dan menunggu moderasi admin.');
    }

    public function edit(int $id)
    {
        $guru = Teacher::where('contributor_user_id', Auth::id())->findOrFail($id);
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');

        return view('pages.kontributor.guru.edit', compact('guru', 'provinces'));
    }

    public function update(Request $request, int $id)
    {
        $guru = Teacher::where('contributor_user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'biografi'    => 'required|string',
            'foto'        => 'nullable|image|max:2048',
            'maps'        => 'nullable|string',
            'tahun_lahir' => 'nullable|integer',
            'province'    => 'nullable|string|max:20',
            'city'        => 'nullable|string|max:20',
            'district'    => 'nullable|string|max:20',
            'village'     => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->imageService->upload($request->file('foto'), 'guru', 'cover', 600, 600, 80, $guru->foto);
        } else {
            unset($validated['foto']);
        }

        $validated['province_code'] = $validated['province'] ?? null;
        $validated['city_code']     = $validated['city'] ?? null;
        $validated['district_code'] = $validated['district'] ?? null;
        $validated['village_code']  = $validated['village'] ?? null;
        unset($validated['province'], $validated['city'], $validated['district'], $validated['village']);

        if ($guru->name !== $validated['name']) {
            $validated['slug'] = Teacher::generateSlug($validated['name'], $id);
        }

        if ($guru->contribution_status === 'rejected') {
            $validated['contribution_status'] = 'pending';
            $validated['rejection_reason']    = null;
        }

        $guru->update($validated);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Data guru berhasil diperbarui.');
    }
}
