<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Assembly;
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
        // Fetch all assemblies
        $assemblies = Assembly::orderBy('nama_majelis')->pluck('nama_majelis', 'id');
        return view('pages.event.create', compact('provinces', 'assemblies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Determine Validation Rules
        $rules = [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'date' => 'required|date',
            'access' => 'required|in:Umum,Khusus',
            'category' => 'required|string|max:255',
            'assembly_id' => 'nullable|exists:assemblies,id',
            'maps_link' => 'nullable|url',
        ];

        // If assembly_id is provided, location fields are nullable (will be inherited)
        if ($request->filled('assembly_id')) {
            $rules['location'] = 'nullable|string|max:255';
            $rules['province'] = 'nullable|string|max:20';
            $rules['city'] = 'nullable|string|max:20';
            $rules['district'] = 'nullable|string|max:20';
            $rules['village'] = 'nullable|string|max:20';
        } else {
            // Otherwise, they are required (as per original logic)
            // Note: Original code had nullable for regions but required for location.
            // I'll keep location required if no assembly.
            $rules['location'] = 'required|string|max:255';
            $rules['province'] = 'nullable|string|max:20';
            $rules['city'] = 'nullable|string|max:20';
            $rules['district'] = 'nullable|string|max:20';
            $rules['village'] = 'nullable|string|max:20';
        }

        $validatedData = $request->validate($rules);
        $dataToCreate = $validatedData;

        // 2. Handle Image Upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';

            // Create Thumbnail
            $thumb = Image::read($file)
                ->scaleDown(800)
                ->toWebp(80);

            // Save to Storage
            Storage::disk('public')->put('events/' . $filename, $thumb);

            $dataToCreate['image'] = 'events/' . $filename;
        }

        // 3. Handle Location Logic
        if ($request->filled('assembly_id')) {
            $assembly = Assembly::find($request->assembly_id);
            if ($assembly) {
                // Inherit from Assembly
                // Use 'nama_majelis' as the location name, or 'alamat' if preferred.
                // Usually location name is the venue name.
                $dataToCreate['location'] = $assembly->nama_majelis; // Or $assembly->alamat? using nama_majelis seems better for "Venue Name"
                $dataToCreate['province_code'] = $assembly->province_code;
                $dataToCreate['city_code'] = $assembly->city_code;
                $dataToCreate['district_code'] = $assembly->district_code;
                $dataToCreate['village_code'] = $assembly->village_code;
            }
        } else {
            // Use Input Data
            $dataToCreate['province_code'] = $validatedData['province'] ?? null;
            $dataToCreate['city_code'] = $validatedData['city'] ?? null;
            $dataToCreate['district_code'] = $validatedData['district'] ?? null;
            $dataToCreate['village_code'] = $validatedData['village'] ?? null;
        }

        // 4. Remove temporary keys
        unset(
            $dataToCreate['province'],
            $dataToCreate['city'],
            $dataToCreate['district'],
            $dataToCreate['village']
        );

        // 5. Create Record
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
        $assemblies = Assembly::orderBy('nama_majelis')->pluck('nama_majelis', 'id');
        return view('pages.event.edit', compact('event', 'provinces', 'assemblies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Similar logic to store, but for update
        $rules = [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'date' => 'required|date',
            'access' => 'required|in:Umum,Khusus',
            'category' => 'required|string|max:255',
            'assembly_id' => 'nullable|exists:assemblies,id',
        ];

        if ($request->filled('assembly_id')) {
            $rules['location'] = 'nullable|string|max:255';
            $rules['province'] = 'nullable|string|max:20';
            $rules['city'] = 'nullable|string|max:20';
            $rules['district'] = 'nullable|string|max:20';
            $rules['village'] = 'nullable|string|max:20';
        } else {
            $rules['location'] = 'required|string|max:255';
            $rules['province'] = 'nullable|string|max:20';
            $rules['city'] = 'nullable|string|max:20';
            $rules['district'] = 'nullable|string|max:20';
            $rules['village'] = 'nullable|string|max:20';
        }

        $validatedData = $request->validate($rules);

        $event = Event::findOrFail($id);
        $dataToUpdate = $validatedData;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';

            $thumb = Image::read($file)
                ->scaleDown(800)
                ->toWebp(80);

            Storage::disk('public')->put('events/' . $filename, (string) $thumb);

            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }

            $dataToUpdate['image'] = 'events/' . $filename;
        } else {
            unset($dataToUpdate['image']);
        }

        // Handle Location Logic
        if ($request->filled('assembly_id')) {
            $assembly = Assembly::find($request->assembly_id);
            if ($assembly) {
                $dataToUpdate['location'] = $assembly->nama_majelis;
                $dataToUpdate['province_code'] = $assembly->province_code;
                $dataToUpdate['city_code'] = $assembly->city_code;
                $dataToUpdate['district_code'] = $assembly->district_code;
                $dataToUpdate['village_code'] = $assembly->village_code;
            }
        } else {
            $dataToUpdate['province_code'] = $validatedData['province'] ?? null;
            $dataToUpdate['city_code'] = $validatedData['city'] ?? null;
            $dataToUpdate['district_code'] = $validatedData['district'] ?? null;
            $dataToUpdate['village_code'] = $validatedData['village'] ?? null;
        }

        unset(
            $dataToUpdate['province'],
            $dataToUpdate['city'],
            $dataToUpdate['district'],
            $dataToUpdate['village']
        );

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
