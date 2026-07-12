<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Teacher;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
        $validated = $request->validate(
            $this->rules($request),
            $this->messages()
        );

        $validated['biografi'] = clean($validated['biografi']);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->imageService->upload($request->file('foto'), 'guru', 'cover', 600, 600);
        }

        if ($request->hasFile('foto_bersama')) {
            $validated['foto_bersama'] = $this->imageService->upload(
                $request->file('foto_bersama'),
                'guru/bersama',
                'scaleDown',
                1600
            );
        } else {
            unset($validated['foto_bersama'], $validated['foto_bersama_caption']);
        }

        unset($validated['hapus_foto_bersama']);

        $validated = $this->mapWilayah($validated);

        $validated['slug'] = Teacher::generateSlug($validated['name']);
        $validated['contributor_user_id'] = Auth::id();
        $validated['contribution_status'] = 'pending';

        $guru = Teacher::create($validated);

        Contribution::create([
            'user_id' => Auth::id(),
            'contributable_id' => $guru->id,
            'contributable_type' => Teacher::class,
            'points_earned' => 0,
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

        $validated = $request->validate(
            $this->rules($request, $guru),
            $this->messages()
        );

        $validated['biografi'] = clean($validated['biografi']);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->imageService->upload($request->file('foto'), 'guru', 'cover', 600, 600, 80, $guru->foto);
        } else {
            unset($validated['foto']);
        }

        // Foto bersama adalah klaim kredibilitas yang tampil publik: penambahan, penggantian,
        // maupun perubahan captionnya harus dimoderasi ulang. Penghapusan tidak.
        $fotoBersamaBerubah = false;

        if ($request->hasFile('foto_bersama')) {
            $validated['foto_bersama'] = $this->imageService->upload(
                $request->file('foto_bersama'),
                'guru/bersama',
                'scaleDown',
                1600,
                null,
                80,
                $guru->foto_bersama
            );
            $fotoBersamaBerubah = true;
        } elseif ($request->boolean('hapus_foto_bersama')) {
            $this->imageService->delete($guru->foto_bersama);
            $validated['foto_bersama'] = null;
            $validated['foto_bersama_caption'] = null;
        } else {
            unset($validated['foto_bersama']);

            if ($guru->foto_bersama) {
                if (($validated['foto_bersama_caption'] ?? null) !== $guru->foto_bersama_caption) {
                    $fotoBersamaBerubah = true;
                }
            } else {
                unset($validated['foto_bersama_caption']);
            }
        }

        unset($validated['hapus_foto_bersama']);

        $validated = $this->mapWilayah($validated);

        if ($guru->name !== $validated['name']) {
            $validated['slug'] = Teacher::generateSlug($validated['name'], $id);
        }

        $kembaliKePending = false;

        if ($guru->contribution_status === 'rejected') {
            $validated['contribution_status'] = 'pending';
            $validated['rejection_reason'] = null;
        } elseif ($guru->contribution_status === 'approved' && $fotoBersamaBerubah) {
            $validated['contribution_status'] = 'pending';
            $kembaliKePending = true;
        }

        $guru->update($validated);

        $message = $kembaliKePending
            ? 'Data guru berhasil diperbarui. Karena foto bersama berubah, manaqib menunggu moderasi ulang dan sementara tidak tampil di publik.'
            : 'Data guru berhasil diperbarui.';

        return redirect()->route('kontributor.saya')->with('success', $message);
    }

    private function rules(Request $request, ?Teacher $guru = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'biografi' => [
                'required',
                'string',
                'max:65000',
                function ($attribute, $value, $fail) {
                    // Editor WYSIWYG mengirim "<p></p>" saat kosong — lolos rule required biasa.
                    if (trim(strip_tags($value)) === '') {
                        $fail('Biografi wajib diisi.');
                    }
                },
            ],
            'foto' => 'nullable|image|max:2048',
            'maps' => 'nullable|string',
            'tahun_lahir' => 'nullable|integer',
            'province' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:20',

            'wafat_masehi' => 'nullable|date',
            'wafat_hijriah_day' => 'nullable|integer|min:1|max:30',
            'wafat_hijriah_month' => 'nullable|integer|min:1|max:12',
            'wafat_hijriah_year' => 'nullable|integer|min:1|max:9999',

            'foto_bersama' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:8192',
            'foto_bersama_caption' => [
                Rule::requiredIf(fn () => $request->hasFile('foto_bersama')
                    || ($guru?->foto_bersama && ! $request->boolean('hapus_foto_bersama'))),
                'nullable',
                'string',
                'max:255',
            ],
            'hapus_foto_bersama' => 'nullable|boolean',
        ];
    }

    private function messages(): array
    {
        return [
            'foto_bersama_caption.required' => 'Keterangan foto bersama wajib diisi.',
            'foto_bersama.max' => 'Ukuran foto bersama maksimal 8 MB.',
            'foto_bersama.mimes' => 'Format foto bersama harus JPG, PNG, atau WebP.',
        ];
    }

    private function mapWilayah(array $validated): array
    {
        $validated['province_code'] = $validated['province'] ?? null;
        $validated['city_code'] = $validated['city'] ?? null;
        $validated['district_code'] = $validated['district'] ?? null;
        $validated['village_code'] = $validated['village'] ?? null;

        unset($validated['province'], $validated['city'], $validated['district'], $validated['village']);

        return $validated;
    }
}
