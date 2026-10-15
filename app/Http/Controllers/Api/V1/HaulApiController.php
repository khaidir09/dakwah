<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HaulRequest;
use App\Http\Resources\Api\V1\HaulResource;
use App\Services\HaulService;
use App\Services\HijriConverter;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HaulApiController extends Controller
{
    public function __construct(
        private readonly HijriConverter $hijri,
        private readonly HaulService $hauls,
    ) {}

    public function __invoke(HaulRequest $request): JsonResponse
    {
        $limit = $request->limit();
        $today = CarbonImmutable::now(HijriConverter::TIMEZONE)->startOfDay();
        $hijriToday = $this->hijri->toHijri($today);

        $payload = Cache::remember(
            'api:v1:haul:'.$today->toDateString().':'.$limit,
            900,
            fn () => $this->hauls->upcoming($today, $limit)
                ->map(fn (array $row) => (new HaulResource($row['teacher'], $row['next'], $row['days']))->resolve())
                ->all()
        );

        return response()->json([
            'data' => $payload,
            'meta' => ['hijri_today' => $hijriToday],
        ]);
    }
}
