<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class KontribusiAcaraController extends Controller
{
    public function create()
    {
        $majelisList = Assembly::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->whereNull('contribution_status')
                    ->orWhere('contribution_status', 'approved');
            })
            ->get();

        if ($majelisList->isEmpty()) {
            return redirect()->route('kontributor.saya')
                ->with('error', 'Anda belum memiliki Majelis yang disetujui. Hanya pengguna yang memiliki Majelis yang dapat membuat Acara.');
        }

        return view('pages.kontributor.acara.create', compact('majelisList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'assembly_id' => 'required|exists:assemblies,id',
            'image'       => 'nullable|image|max:2048',
            'date'        => 'required|date',
            'access'      => 'required|in:Umum,Khusus',
            'category'    => 'required|string|max:255',
        ]);

        $assembly = Assembly::where('id', $validated['assembly_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $assembly) {
            return back()->withErrors(['assembly_id' => 'Majelis tidak valid.']);
        }

        $validated['location']        = $assembly->alamat;
        $validated['province_code']   = $assembly->province_code;
        $validated['city_code']       = $assembly->city_code;
        $validated['district_code']   = $assembly->district_code;
        $validated['village_code']    = $assembly->village_code;
        $validated['user_id']         = Auth::id();
        $validated['status']          = 'pending';

        if ($request->hasFile('image')) {
            $filename = Str::uuid() . '.webp';
            $img = Image::read($request->file('image'))->scaleDown(800)->toWebp(80);
            Storage::disk('public')->put('events/' . $filename, (string) $img);
            $validated['image'] = 'events/' . $filename;
        } else {
            unset($validated['image']);
        }

        $acara = Event::create($validated);

        Contribution::create([
            'user_id'           => Auth::id(),
            'contributable_id'  => $acara->id,
            'contributable_type' => Event::class,
            'points_earned'     => 0,
        ]);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Acara berhasil dikirim dan menunggu moderasi admin.');
    }

    public function edit(int $id)
    {
        $acara = Event::where('user_id', Auth::id())->findOrFail($id);

        $majelisList = Assembly::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->whereNull('contribution_status')
                    ->orWhere('contribution_status', 'approved');
            })
            ->get();

        return view('pages.kontributor.acara.edit', compact('acara', 'majelisList'));
    }

    public function update(Request $request, int $id)
    {
        $acara = Event::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'assembly_id' => 'required|exists:assemblies,id',
            'image'       => 'nullable|image|max:2048',
            'date'        => 'required|date',
            'access'      => 'required|in:Umum,Khusus',
            'category'    => 'required|string|max:255',
        ]);

        $assembly = Assembly::where('id', $validated['assembly_id'])
            ->where('user_id', Auth::id())
            ->first();

        if (! $assembly) {
            return back()->withErrors(['assembly_id' => 'Majelis tidak valid.']);
        }

        $validated['location']      = $assembly->alamat;
        $validated['province_code'] = $assembly->province_code;
        $validated['city_code']     = $assembly->city_code;
        $validated['district_code'] = $assembly->district_code;
        $validated['village_code']  = $assembly->village_code;

        if ($request->hasFile('image')) {
            $filename = Str::uuid() . '.webp';
            $img = Image::read($request->file('image'))->scaleDown(800)->toWebp(80);
            Storage::disk('public')->put('events/' . $filename, (string) $img);
            if ($acara->image) {
                Storage::disk('public')->delete($acara->image);
            }
            $validated['image'] = 'events/' . $filename;
        } else {
            unset($validated['image']);
        }

        if ($acara->status === 'rejected') {
            $validated['status']           = 'pending';
            $validated['rejection_reason'] = null;
        }

        $acara->update($validated);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Acara berhasil diperbarui.');
    }
}
