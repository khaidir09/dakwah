<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Contribution;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Laravolt\Indonesia\Models\Province;

class KontribusiAcaraController extends Controller
{
    public function create()
    {
        return view('pages.kontributor.acara.create', [
            'majelisList' => $this->eligibleAssemblies(),
            'provinces' => $this->provinces(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules($request), [
            'date.after_or_equal' => 'Tanggal acara harus minimal 7 hari dari hari ini.',
        ]);

        $assembly = $this->resolveAssembly($request);
        if ($request->filled('assembly_id') && ! $assembly) {
            return back()->withErrors(['assembly_id' => 'Majelis tidak valid.'])->withInput();
        }

        $data = $this->prepareData($validated, $assembly);
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        if ($path = $this->handleImageUpload($request)) {
            $data['image'] = $path;
        }

        $acara = Event::create($data);

        Contribution::create([
            'user_id' => Auth::id(),
            'contributable_id' => $acara->id,
            'contributable_type' => Event::class,
            'points_earned' => 0,
        ]);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Acara berhasil dikirim dan menunggu moderasi admin.');
    }

    public function edit(int $id)
    {
        $acara = Event::where('user_id', Auth::id())->findOrFail($id);

        return view('pages.kontributor.acara.edit', [
            'acara' => $acara,
            'majelisList' => $this->eligibleAssemblies(),
            'provinces' => $this->provinces(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $acara = Event::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate($this->validationRules($request), [
            'date.after_or_equal' => 'Tanggal acara harus minimal 7 hari dari hari ini.',
        ]);

        $assembly = $this->resolveAssembly($request);
        if ($request->filled('assembly_id') && ! $assembly) {
            return back()->withErrors(['assembly_id' => 'Majelis tidak valid.'])->withInput();
        }

        $data = $this->prepareData($validated, $assembly);

        if ($path = $this->handleImageUpload($request, $acara->image)) {
            $data['image'] = $path;
        }

        if ($acara->status === 'rejected') {
            $data['status'] = 'pending';
            $data['rejection_reason'] = null;
        }

        $acara->update($data);

        return redirect()->route('kontributor.saya')
            ->with('success', 'Acara berhasil diperbarui.');
    }

    /**
     * Majelis yang boleh dipilih kontributor: yang dimilikinya atau yang diikutinya,
     * dibatasi pada majelis publik (belum dimoderasi atau sudah disetujui).
     */
    private function eligibleAssemblies()
    {
        $userId = Auth::id();

        return Assembly::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhereHas('followers', fn ($f) => $f->where('users.id', $userId));
        })
            ->where(function ($q) {
                $q->whereNull('contribution_status')
                    ->orWhere('contribution_status', 'approved');
            })
            ->orderBy('nama_majelis')
            ->get();
    }

    private function provinces()
    {
        return Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
    }

    private function validationRules(Request $request): array
    {
        return [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'date' => $this->dateRules(),
            'access' => 'required|in:Umum,Khusus',
            'category' => 'required|string|max:255',
            'assembly_id' => 'nullable|exists:assemblies,id',
            'maps_link' => 'nullable|url',
            'location' => $request->filled('assembly_id') ? 'nullable|string|max:255' : 'required|string|max:255',
            'province' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:20',
        ];
    }

    /**
     * Validasi kepemilikan: majelis hanya valid jika dimiliki atau diikuti kontributor.
     */
    private function resolveAssembly(Request $request): ?Assembly
    {
        if (! $request->filled('assembly_id')) {
            return null;
        }

        $userId = Auth::id();

        return Assembly::where('id', $request->assembly_id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('followers', fn ($f) => $f->where('users.id', $userId));
            })
            ->first();
    }

    /**
     * Susun data event: warisi lokasi dari majelis bila dipilih, jika tidak gunakan input manual.
     */
    private function prepareData(array $validated, ?Assembly $assembly): array
    {
        $data = $validated;
        unset($data['province'], $data['city'], $data['district'], $data['village'], $data['image']);

        if ($assembly) {
            $data['assembly_id'] = $assembly->id;
            $data['location'] = $assembly->nama_majelis;
            $data['province_code'] = $assembly->province_code;
            $data['city_code'] = $assembly->city_code;
            $data['district_code'] = $assembly->district_code;
            $data['village_code'] = $assembly->village_code;
        } else {
            $data['assembly_id'] = null;
            $data['province_code'] = $validated['province'] ?? null;
            $data['city_code'] = $validated['city'] ?? null;
            $data['district_code'] = $validated['district'] ?? null;
            $data['village_code'] = $validated['village'] ?? null;
        }

        return $data;
    }

    /**
     * Acara harus dijadwalkan minimal 7 hari dari hari ini, kecuali untuk Super Admin.
     */
    private function dateRules(): array
    {
        $rules = ['required', 'date'];

        if (! Auth::user()->hasRole('Super Admin')) {
            $rules[] = 'after_or_equal:'.Carbon::today()->addDays(7)->toDateString();
        }

        return $rules;
    }

    private function handleImageUpload(Request $request, ?string $oldImagePath = null): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $filename = Str::uuid().'.webp';
        $img = Image::read($request->file('image'))->scaleDown(800)->toWebp(80);
        Storage::disk('public')->put('events/'.$filename, (string) $img);

        if ($oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return 'events/'.$filename;
    }
}
