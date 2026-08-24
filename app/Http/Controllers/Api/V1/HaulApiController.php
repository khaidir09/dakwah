<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HaulRequest;
use App\Http\Resources\Api\V1\HaulResource;
use App\Models\Teacher;
use App\Services\HijriConverter;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HaulApiController extends Controller
{
    public function __construct(private readonly HijriConverter $hijri) {}

    public function __invoke(HaulRequest $request): JsonResponse
    {
        $limit = $request->limit();
        $today = CarbonImmutable::now(HijriConverter::TIMEZONE)->startOfDay();
        $hijriToday = $this->hijri->toHijri($today);

        $payload = Cache::remember(
            'api:v1:haul:'.$today->toDateString().':'.$limit,
            900,
            fn () => $this->build($today, $hijriToday, $limit)
        );

        return response()->json([
            'data' => $payload,
            'meta' => ['hijri_today' => $hijriToday],
        ]);
    }

    private function build(CarbonImmutable $today, array $hijriToday, int $limit): array
    {
        $teachers = Teacher::query()
            ->publiclyVisible()
            ->whereNotNull('wafat_hijriah_year')
            ->whereNotNull('wafat_hijriah_day')
            ->whereNotNull('wafat_hijriah_month')
            ->get();

        $upcoming = $teachers
            ->map(function (Teacher $teacher) use ($today, $hijriToday) {
                $next = $this->hijri->toGregorian(
                    $teacher->wafat_hijriah_day,
                    $teacher->wafat_hijriah_month,
                    $hijriToday['year']
                );

                // Haul tahun ini sudah lewat -> pakai tahun Hijriah berikutnya.
                if ($next->lt($today)) {
                    $next = $this->hijri->toGregorian(
                        $teacher->wafat_hijriah_day,
                        $teacher->wafat_hijriah_month,
                        $hijriToday['year'] + 1
                    );
                }

                return [
                    'teacher' => $teacher,
                    'next' => $next,
                    'days' => (int) $today->diffInDays($next, false),
                ];
            })
            ->sortBy('days')
            ->take($limit)
            ->values();

        return $upcoming
            ->map(fn (array $row) => (new HaulResource($row['teacher'], $row['next'], $row['days']))->resolve())
            ->all();
    }
}
