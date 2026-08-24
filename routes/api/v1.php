<?php

use App\Http\Controllers\Api\V1\HaulApiController;
use App\Http\Controllers\Api\V1\JadwalMajelisApiController;
use App\Http\Controllers\Api\V1\WiridApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Publik v1
|--------------------------------------------------------------------------
|
| Kontrak stabil untuk konsumen luar. Hanya perubahan additive yang boleh
| masuk ke v1; perubahan breaking harus lahir sebagai /api/v2.
| Lihat docs/specs/api-publik-jadwal-sholat-tv.md
|
*/

Route::prefix('v1')
    ->middleware(['api.version:1', 'api.client', 'throttle:api-public', 'etag'])
    ->group(function () {
        Route::get('/jadwal-majelis', JadwalMajelisApiController::class)->name('api.v1.jadwal-majelis');
        Route::get('/wirid', WiridApiController::class)->name('api.v1.wirid');
        Route::get('/haul', HaulApiController::class)->name('api.v1.haul');
    });
