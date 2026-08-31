<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Event;
use App\Models\EventPosterGeneration;
use App\Services\GeminiPosterService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventPosterController extends Controller
{
    public function __construct(private GeminiPosterService $poster) {}

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $this->poster->isEligible($user)) {
            abort(403, 'Fitur poster AI hanya untuk pengurus majelis dan kontributor.');
        }

        $quota = $this->poster->quotaFor($user);

        if (! $quota['available']) {
            return response()->json([
                'message' => 'Fitur pembuatan poster sedang tidak tersedia. Silakan unggah poster sendiri.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'date' => 'required|date',
            'style' => ['required', Rule::in(array_keys(GeminiPosterService::STYLES))],
            'assembly_id' => 'nullable|integer|exists:assemblies,id',
            'event_id' => 'nullable|integer|exists:events,id',
        ]);

        // Poster yang dibuat dari form tambah acara lalu ditinggalkan tidak pernah
        // terpakai. Dibersihkan di sini, bukan lewat scheduler, karena tidak ada
        // jaminan cron berjalan di produksi.
        $this->cleanUpOrphans($user->id);

        if ($quota['remaining'] < 1) {
            return response()->json([
                'message' => sprintf(
                    'Kuota pembuatan poster bulan ini sudah habis (%d dari %d). Kuota diperbarui setiap awal bulan.',
                    $quota['used'],
                    $quota['total']
                ),
                'quota' => $quota,
            ], 422);
        }

        $assembly = $this->resolveAssembly($validated['assembly_id'] ?? null, $user->id);
        $event = $this->resolveEvent($validated['event_id'] ?? null, $user->id);

        $prompt = $this->poster->buildPrompt([
            'name' => $validated['name'],
            'date' => $validated['date'],
            'style' => $validated['style'],
            'assembly' => $assembly?->nama_majelis,
            'location' => $event?->location,
        ]);

        $result = $this->poster->generate($prompt);

        $generation = EventPosterGeneration::create([
            'user_id' => $user->id,
            'event_id' => $event?->id,
            'assembly_id' => $assembly?->id,
            'style' => $validated['style'],
            'prompt' => $prompt,
            'model' => $this->poster->model(),
            'image_path' => $result['path'],
            'status' => $result['ok']
                ? EventPosterGeneration::STATUS_SUCCESS
                : EventPosterGeneration::STATUS_FAILED,
            'error_message' => $result['error'] ? Str::limit($result['error'], 1000, '') : null,
        ]);

        if (! $result['ok']) {
            // Baris gagal tidak dihitung terhadap kuota — biaya API tidak terpakai
            // dan pengguna tidak boleh dirugikan oleh gangguan di sisi layanan.
            return response()->json([
                'message' => 'Pembuatan poster gagal. Silakan coba lagi beberapa saat atau unggah poster sendiri.',
            ], 502);
        }

        return response()->json([
            'generation_id' => $generation->id,
            'preview_url' => Storage::url($generation->image_path),
            'quota' => $this->poster->quotaFor($user),
        ]);
    }

    /**
     * Majelis hanya dipakai sebagai konteks prompt, jadi cukup dipastikan benar-benar
     * milik atau diikuti pengguna — bukan sekadar id yang ada di tabel.
     */
    private function resolveAssembly(?int $assemblyId, int $userId): ?Assembly
    {
        if (! $assemblyId) {
            return null;
        }

        return Assembly::where('id', $assemblyId)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('followers', fn($f) => $f->where('users.id', $userId));
            })
            ->first();
    }

    private function resolveEvent(?int $eventId, int $userId): ?Event
    {
        if (! $eventId) {
            return null;
        }

        return Event::where('id', $eventId)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('assembly', fn($a) => $a->where('user_id', $userId));
            })
            ->first();
    }

    private function cleanUpOrphans(int $userId): void
    {
        $orphans = EventPosterGeneration::where('user_id', $userId)
            ->whereNull('event_id')
            ->where('status', EventPosterGeneration::STATUS_SUCCESS)
            ->where('created_at', '<', Carbon::now()->subDay())
            ->get();

        foreach ($orphans as $orphan) {
            $this->poster->deletePoster($orphan->image_path);
            $orphan->delete();
        }
    }
}
