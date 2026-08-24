<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\JadwalMajelisRequest;
use App\Http\Resources\Api\V1\ScheduleOccurrenceResource;
use App\Models\Schedule;
use App\Services\HijriConverter;
use App\Services\HijriService;
use App\Services\ScheduleOccurrenceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class JadwalMajelisApiController extends Controller
{
    /**
     * Nilai `access` yang boleh keluar ke pihak ketiga. Sengaja allowlist:
     * nilai baru yang belum dikenal otomatis tersembunyi.
     */
    private const PUBLIC_ACCESS = ['Umum', 'Ikhwan', 'Akhwat'];

    public function __construct(
        private readonly ScheduleOccurrenceService $occurrences,
        private readonly HijriService $hijriService,
    ) {}

    public function __invoke(JadwalMajelisRequest $request): JsonResponse
    {
        $days = $request->days();
        $from = CarbonImmutable::now(HijriConverter::TIMEZONE)->startOfDay();
        $to = $from->addDays($days - 1);

        $filters = [
            'city_code' => $request->validated('city_code'),
            'district_code' => $request->validated('district_code'),
            'tipe' => $request->validated('tipe'),
            'days' => $days,
        ];

        $payload = Cache::remember($this->cacheKey($filters, $from), 900, function () use ($filters, $from, $to) {
            $schedules = $this->query($filters)->get();

            return array_map(
                fn (array $occurrence) => (new ScheduleOccurrenceResource($occurrence['schedule'], $occurrence['date']))->resolve(),
                $this->occurrences->expand($schedules, $from, $to)
            );
        });

        return response()->json([
            'data' => $payload,
            'meta' => [
                'city_code' => $filters['city_code'],
                'district_code' => $filters['district_code'],
                'days' => $days,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'is_ramadhan' => $this->isRamadhan(),
            ],
        ]);
    }

    private function query(array $filters)
    {
        return Schedule::query()
            ->publiclyVisible()
            ->where('status', 'Aktif')
            ->whereIn('access', self::PUBLIC_ACCESS)
            ->whereHas('assembly', function ($q) use ($filters) {
                $q->publiclyVisible()->where('city_code', $filters['city_code']);

                if ($filters['district_code']) {
                    $q->where('district_code', $filters['district_code']);
                }

                if ($filters['tipe']) {
                    $q->where('tipe', $filters['tipe']);
                }
            })
            ->with(['assembly', 'teacher']);
    }

    /**
     * Tanggal ikut masuk key agar pergantian hari membatalkan cache dengan sendirinya.
     */
    private function cacheKey(array $filters, CarbonImmutable $from): string
    {
        ksort($filters);

        return 'api:v1:jadwal:'.$from->toDateString().':'.md5(json_encode($filters));
    }

    /**
     * Bukan data kritis — kegagalan API kalender tidak boleh menjatuhkan endpoint.
     */
    private function isRamadhan(): bool
    {
        try {
            return $this->hijriService->isRamadhan();
        } catch (\Throwable) {
            return false;
        }
    }
}
