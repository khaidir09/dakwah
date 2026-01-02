<?php

namespace App\Http\Controllers\User;

use App\Models\Event;
use App\Models\Assembly;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ManageEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.user.kelola-acara.acara');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.user.kelola-acara.tambah-acara');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'date' => 'required|date',
            'access' => 'required|in:Umum,Khusus',
            'category' => 'required|string|max:255',
        ]);

        $assembly = Assembly::where('user_id', Auth::id())->first();
        if (!$assembly) {
            return redirect()->back()->withErrors(['message' => 'Anda belum memiliki Majelis.']);
        }

        $dataToCreate = $validatedData;

        $dataToCreate['assembly_id'] = $assembly->id;
        $dataToCreate['location'] = $assembly->alamat;
        $dataToCreate['province_code'] = $assembly->province_code;
        $dataToCreate['city_code'] = $assembly->city_code;
        $dataToCreate['district_code'] = $assembly->district_code;
        $dataToCreate['village_code'] = $assembly->village_code;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';

            // 1. Buat Versi THUMBNAIL (Untuk List/Avatar) - Crop Persegi
            // Note: Adjust size as needed for events (maybe landscape?)
            // Guru uses 600x600. Events often need 16:9. Let's stick to cover style or standard.
            // But to "mimic guru", I'll use similar logic but maybe different dimensions if appropriate?
            // "Guru" uses 600x600 cover. Let's use that for now to be safe, or 800x600.
            $thumb = Image::read($file)
                ->scaleDown(800)
                ->toWebp(80);

            // 3. Simpan ke Storage (Folder public)
            Storage::disk('public')->put('events/' . $filename, $thumb);

            $dataToCreate['image'] = 'events/' . $filename;
        }

        // 6. Buat record baru di database
        Event::create($dataToCreate);

        return redirect()->route('kelola-acara-majelis')->with('message', 'Event berhasil ditambahkan!');
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
        $event = Event::where('id', $id)->firstOrFail();
        $assembly = Assembly::where('user_id', Auth::id())->first();
        if (!$assembly || $event->assembly_id !== $assembly->id) {
            abort(403, 'Unauthorized');
        }
        return view('pages.user.kelola-acara.edit-acara', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'date' => 'required|date',
            'access' => 'required|in:Umum,Khusus',
            'category' => 'required|string|max:255',
        ]);

        $event = Event::findOrFail($id);

        $assembly = Assembly::where('user_id', Auth::id())->first();
        if (!$assembly || $event->assembly_id !== $assembly->id) {
            abort(403, 'Unauthorized');
        }

        $dataToUpdate = $validatedData;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';

            // A. Proses Gambar Baru (Sama seperti store)
            $thumb = Image::read($file)
                ->scaleDown(800)
                ->toWebp(80);

            // B. Simpan Gambar Baru
            Storage::disk('public')->put('events/' . $filename, (string) $thumb);

            // C. Hapus Gambar Lama (PENTING)
            if ($event->image) {
                // Hapus file 'large' (sesuai path di database)
                Storage::disk('public')->delete($event->image);
            }

            // D. Update array data dengan path baru
            $dataToUpdate['image'] = 'events/' . $filename;
        } else {
            // Jika tidak ada file baru, hapus 'image' dari array
            // agar tidak menimpa file yang ada dengan nilai null.
            unset($dataToUpdate['image']);
        }

        // 7. Update data event
        $event->update($dataToUpdate);

        return redirect()->route('kelola-acara-majelis')->with('message', 'Event berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Handled by Livewire
    }
}
