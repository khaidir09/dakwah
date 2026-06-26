<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Contribution;
use App\Models\Teacher;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravolt\Indonesia\Models\Province;

class KontribusiMajelisController extends Controller
{
    use HandlesImageUploads;

    public function create()
    {
        $teachers = Teacher::publiclyVisible()->where('wafat_masehi', null)->get();
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');

        return view('pages.kontributor.majelis.create', compact('teachers', 'provinces'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_majelis'      => 'required|string|max:255',
            'tipe'              => 'nullable|string|in:Majelis,Mesjid,Langgar,Musholla',
            'deskripsi'         => 'required|string',
            'teacher_id'        => 'nullable|required_without:custom_leader_name|exists:teachers,id',
            'custom_leader_name'=> 'nullable|required_without:teacher_id|string|max:255',
            'alamat'            => 'required|string',
            'maps'              => 'nullable|string|max:255',
            'gambar'            => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'province'          => 'nullable|string|max:20',
            'city'              => 'nullable|string|max:20',
            'district'          => 'nullable|string|max:20',
            'village'           => 'nullable|string|max:20',
        ]);

        if (! empty($validated['teacher_id'])) {
            $validated['custom_leader_name'] = null;
        } else {
            $validated['teacher_id'] = null;
        }

        if ($request->hasFile('gambar')) {
            $paths = $this->uploadImageWithThumbnail($request->file('gambar'), 'majelis');
            $validated['gambar'] = $paths['large'];
        } else {
            unset($validated['gambar']);
        }

        $validated['province_code'] = $validated['province'] ?? null;
        $validated['city_code']     = $validated['city'] ?? null;
        $validated['district_code'] = $validated['district'] ?? null;
        $validated['village_code']  = $validated['village'] ?? null;
        unset($validated['province'], $validated['city'], $validated['district'], $validated['village']);

        $validated['user_id']             = Auth::id();
        $validated['contribution_status'] = 'pending';

        $majelis = Assembly::create($validated);

        Contribution::create([
            'user_id'           => Auth::id(),
            'contributable_id'  => $majelis->id,
            'contributable_type'=> Assembly::class,
            'points_earned'     => 0,
        ]);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Majelis berhasil dikirim dan menunggu moderasi admin.');
    }

    public function edit(int $id)
    {
        $majelis = Assembly::where('user_id', Auth::id())
            ->whereNotNull('contribution_status')
            ->findOrFail($id);

        $teachers = Teacher::publiclyVisible()->where('wafat_masehi', null)->get();
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');

        return view('pages.kontributor.majelis.edit', compact('majelis', 'teachers', 'provinces'));
    }

    public function update(Request $request, int $id)
    {
        $majelis = Assembly::where('user_id', Auth::id())
            ->whereNotNull('contribution_status')
            ->findOrFail($id);

        $validated = $request->validate([
            'nama_majelis'      => 'required|string|max:255',
            'tipe'              => 'nullable|string|in:Majelis,Mesjid,Langgar,Musholla',
            'deskripsi'         => 'required|string',
            'teacher_id'        => 'nullable|required_without:custom_leader_name|exists:teachers,id',
            'custom_leader_name'=> 'nullable|required_without:teacher_id|string|max:255',
            'alamat'            => 'required|string',
            'maps'              => 'nullable|string|max:255',
            'gambar'            => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'province'          => 'nullable|string|max:20',
            'city'              => 'nullable|string|max:20',
            'district'          => 'nullable|string|max:20',
            'village'           => 'nullable|string|max:20',
        ]);

        if (! empty($validated['teacher_id'])) {
            $validated['custom_leader_name'] = null;
        } else {
            $validated['teacher_id'] = null;
        }

        if ($request->hasFile('gambar')) {
            if ($majelis->gambar) {
                $this->deleteImageWithThumbnail($majelis->gambar);
            }
            $paths = $this->uploadImageWithThumbnail($request->file('gambar'), 'majelis');
            $validated['gambar'] = $paths['large'];
        } else {
            unset($validated['gambar']);
        }

        $validated['province_code'] = $validated['province'] ?? null;
        $validated['city_code']     = $validated['city'] ?? null;
        $validated['district_code'] = $validated['district'] ?? null;
        $validated['village_code']  = $validated['village'] ?? null;
        unset($validated['province'], $validated['city'], $validated['district'], $validated['village']);

        if ($majelis->contribution_status === 'rejected') {
            $validated['contribution_status'] = 'pending';
            $validated['rejection_reason']    = null;
        }

        $majelis->update($validated);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Majelis berhasil diperbarui dan dikirim ulang untuk moderasi.');
    }
}
