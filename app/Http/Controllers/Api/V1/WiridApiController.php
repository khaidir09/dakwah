<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WiridRequest;
use App\Http\Resources\Api\V1\WiridResource;
use App\Models\Wirid;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class WiridApiController extends Controller
{
    public function __invoke(WiridRequest $request): JsonResponse
    {
        $filters = [
            'kategori' => $request->validated('kategori'),
            'waktu' => $request->validated('waktu'),
            'per_page' => $request->perPage(),
            'page' => (int) ($request->validated('page') ?? 1),
        ];

        $payload = Cache::remember($this->cacheKey($filters), 900, function () use ($filters) {
            $paginator = Wirid::query()
                ->publiclyVisible()
                ->when($filters['kategori'], fn ($q) => $q->where('kategori', $filters['kategori']))
                ->when($filters['waktu'], fn ($q) => $q->where('waktu', $filters['waktu']))
                ->latest()
                ->paginate($filters['per_page'], ['*'], 'page', $filters['page']);

            return [
                'data' => WiridResource::collection($paginator->getCollection())->resolve(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ];
        });

        return response()->json($payload);
    }

    private function cacheKey(array $filters): string
    {
        ksort($filters);

        return 'api:v1:wirid:'.md5(json_encode($filters));
    }
}
