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
        $validatedData = $request->validate($this->getValidationRules($request));
        $dataToCreate = $validatedData;

        // 2. Handle Image Upload
        $uploadedImage = $this->handleImageUpload($request);
        if ($uploadedImage) {
            $dataToCreate['image'] = $uploadedImage;
        }

        // 3. Handle Location Logic
        $dataToCreate = array_merge($dataToCreate, $this->resolveLocationData($request, $validatedData));

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
        // 1. Determine Validation Rules
        $validatedData = $request->validate($this->getValidationRules($request));

        $event = Event::findOrFail($id);
        $dataToUpdate = $validatedData;

        $uploadedImage = $this->handleImageUpload($request, $event);
        if ($uploadedImage) {
            $dataToUpdate['image'] = $uploadedImage;
        } else {
            unset($dataToUpdate['image']);
        }

        // Handle Location Logic
        $dataToUpdate = array_merge($dataToUpdate, $this->resolveLocationData($request, $validatedData));

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

    /**
     * Get validation rules for store and update methods.
     */
    private function getValidationRules(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'date' => 'required|date',
            'access' => 'required|in:Umum,Khusus',
            'category' => 'required|string|max:255',
            'assembly_id' => 'nullable|exists:assemblies,id',
            'maps_link' => 'nullable|url',
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

        return $rules;
    }

    /**
     * Handle image upload, scaling, conversion, and optional deletion of the old image.
     */
    private function handleImageUpload(Request $request, ?Event $event = null): ?string
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.webp';

            $thumb = Image::read($file)
                ->scaleDown(800)
                ->toWebp(80);

            Storage::disk('public')->put('events/' . $filename, (string) $thumb);

            if ($event && $event->image) {
                Storage::disk('public')->delete($event->image);
            }

            return 'events/' . $filename;
        }

        return null;
    }

    /**
     * Resolve location data either from a related Assembly or from provided input data.
     */
    private function resolveLocationData(Request $request, array $validatedData): array
    {
        $locationData = [];

        if ($request->filled('assembly_id')) {
            $assembly = Assembly::find($request->assembly_id);
            if ($assembly) {
                $locationData['location'] = $assembly->nama_majelis;
                $locationData['province_code'] = $assembly->province_code;
                $locationData['city_code'] = $assembly->city_code;
                $locationData['district_code'] = $assembly->district_code;
                $locationData['village_code'] = $assembly->village_code;
            }
        } else {
            $locationData['province_code'] = $validatedData['province'] ?? null;
            $locationData['city_code'] = $validatedData['city'] ?? null;
            $locationData['district_code'] = $validatedData['district'] ?? null;
            $locationData['village_code'] = $validatedData['village'] ?? null;
        }

        return $locationData;
    }
}
