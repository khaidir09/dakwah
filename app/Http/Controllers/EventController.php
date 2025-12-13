<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Intervention\Image\Laravel\Facades\Image;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.event.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
        return view('pages.event.create', compact('provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'access' => 'required|in:Umum,Khusus',
            'category' => 'required|string|max:255',
            'province' => 'nullable|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'village' => 'nullable|string',
        ]);

        $dataToCreate = $validatedData;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';

            // 1. Buat Versi THUMBNAIL (Untuk List/Avatar) - Crop Persegi
            // Note: Adjust size as needed for events (maybe landscape?)
            // Guru uses 600x600. Events often need 16:9. Let's stick to cover style or standard.
            // But to "mimic guru", I'll use similar logic but maybe different dimensions if appropriate?
            // "Guru" uses 600x600 cover. Let's use that for now to be safe, or 800x600.
            $thumb = Image::read($file)
                ->cover(800, 600)
                ->toWebp(80);

            // 3. Simpan ke Storage (Folder public)
            Storage::disk('public')->put('events/' . $filename, $thumb);

            $dataToCreate['image'] = 'events/' . $filename;
        }

        $dataToCreate['province_code'] = $validatedData['province'] ?? null;
        $dataToCreate['city_code'] = $validatedData['city'] ?? null;
        $dataToCreate['district_code'] = $validatedData['district'] ?? null;
        $dataToCreate['village_code'] = $validatedData['village'] ?? null;

        // 5. Hapus key lama agar tidak error saat create
        unset(
            $dataToCreate['province'],
            $dataToCreate['city'],
            $dataToCreate['district'],
            $dataToCreate['village']
        );

        // 6. Buat record baru di database
        Event::create($dataToCreate);

        return redirect()->route('event.index')->with('message', 'Event berhasil ditambahkan!');
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
        $event = Event::findOrFail($id);
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
        return view('pages.event.edit', compact('event', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'access' => 'required|in:Umum,Khusus',
            'category' => 'required|string|max:255',
            'province' => 'nullable|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'village' => 'nullable|string',
        ]);

        $event = Event::findOrFail($id);

        $dataToUpdate = $validatedData;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';

            // A. Proses Gambar Baru (Sama seperti store)
            $thumb = Image::read($file)
                ->cover(800, 600)
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

        // 5. Map data wilayah
        $dataToUpdate['province_code'] = $validatedData['province'] ?? null;
        $dataToUpdate['city_code'] = $validatedData['city'] ?? null;
        $dataToUpdate['district_code'] = $validatedData['district'] ?? null;
        $dataToUpdate['village_code'] = $validatedData['village'] ?? null;

        // 6. Hapus key form
        unset(
            $dataToUpdate['province'],
            $dataToUpdate['city'],
            $dataToUpdate['district'],
            $dataToUpdate['village']
        );

        // 7. Update data event
        $event->update($dataToUpdate);

        return redirect()->route('event.index')->with('message', 'Event berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Handled by Livewire
    }
}
