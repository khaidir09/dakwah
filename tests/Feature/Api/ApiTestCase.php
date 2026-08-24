<?php

namespace Tests\Feature\Api;

use App\Models\ApiClient;
use App\Models\Assembly;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected const KEY = 'testkey1abcdefghijklmnopqrstuvwxyz012345';

    protected const PROVINCE = '63';

    protected const CITY = '6371';

    protected const DISTRICT = '6371011';

    protected ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);

        $this->client = ApiClient::create([
            'name' => 'Test Partner',
            'key_prefix' => substr(self::KEY, 0, ApiClient::PREFIX_LENGTH),
            'key_hash' => Hash::make(self::KEY),
            'rate_limit_per_minute' => 60,
        ]);

        $this->seedRegions();
    }

    /**
     * Tabel wilayah laravolt kosong di database test, sedangkan validasi
     * memakai rule `exists` terhadapnya.
     */
    protected function seedRegions(): void
    {
        DB::table('indonesia_provinces')->insert([
            'code' => self::PROVINCE, 'name' => 'Kalimantan Selatan',
        ]);
        DB::table('indonesia_cities')->insert([
            'code' => self::CITY, 'province_code' => self::PROVINCE, 'name' => 'Kota Banjarmasin',
        ]);
        DB::table('indonesia_districts')->insert([
            'code' => self::DISTRICT, 'city_code' => self::CITY, 'name' => 'Banjarmasin Tengah',
        ]);
    }

    protected function apiGet(string $uri, array $headers = [])
    {
        return $this->getJson($uri, array_merge(['X-API-Key' => self::KEY], $headers));
    }

    protected function makeAssembly(array $attributes = []): Assembly
    {
        return Assembly::create(array_merge([
            'nama_majelis' => 'Majelis Uji',
            'deskripsi' => 'Deskripsi',
            'guru' => 'Guru',
            'alamat' => 'Jalan Uji',
            'maps' => 'https://maps.example/x',
            'tipe' => 'Majelis',
            'status' => 'Aktif',
            'province_code' => self::PROVINCE,
            'city_code' => self::CITY,
            'district_code' => self::DISTRICT,
        ], $attributes));
    }

    protected function makeTeacher(array $attributes = []): Teacher
    {
        $name = $attributes['name'] ?? 'Guru Uji';

        return Teacher::create(array_merge([
            'name' => $name,
            'slug' => Teacher::generateSlug($name),
            'biografi' => 'Biografi',
            'foto' => 'guru/uji.webp',
        ], $attributes));
    }

    /**
     * Jadwal mingguan yang pasti jatuh pada besok, agar selalu berada
     * di dalam jendela default 7 hari tanpa bergantung hari menjalankan test.
     */
    protected function makeSchedule(Assembly $assembly, array $attributes = []): Schedule
    {
        $tomorrow = now()->addDay();

        return Schedule::create(array_merge([
            'nama_jadwal' => 'Kajian Uji',
            'deskripsi' => 'Deskripsi kajian',
            'assembly_id' => $assembly->id,
            'waktu' => '2026-01-01 19:30:00',
            'hari' => $tomorrow->locale('id')->isoFormat('dddd'),
            'access' => 'Umum',
            'status' => 'Aktif',
            'recurrence_type' => 'weekly',
            'calendar_system' => 'gregorian',
        ], $attributes));
    }
}
