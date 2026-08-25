<?php

namespace Tests\Feature;

use App\Models\Assembly;
use App\Models\Event;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Basis untuk test halaman publik: seed wilayah, patok tanggal Hijriah,
 * dan fixture entitas yang dipakai lintas test SEO maupun visibilitas.
 */
abstract class PublicPageTestCase extends TestCase
{
    use RefreshDatabase;

    protected const PROVINCE = '63';

    protected const CITY = '6371';

    protected const DISTRICT = '6371011';

    protected const VILLAGE = '6371011001';

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        // HijriService dan HomeUpcomingHaul memanggil api.myquran.com saat render.
        Http::fake([
            'api.myquran.com/*' => Http::response([
                'data' => ['hijr' => ['today' => 'Rabu, 17 Syakban 1447 H']],
            ]),
            '*' => Http::response([], 200),
        ]);

        $this->seedRegions();
    }

    /**
     * Tabel wilayah laravolt kosong di database test, sedangkan view publik
     * mengakses relasi village/district tanpa null-safe.
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
        DB::table('indonesia_villages')->insert([
            'code' => self::VILLAGE, 'district_code' => self::DISTRICT, 'name' => 'Antasan Besar',
        ]);
    }

    protected function kontributor(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Kontributor');

        return $user;
    }

    protected function superAdmin(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Super Admin');

        return $user;
    }

    protected function makeAssembly(array $attributes = []): Assembly
    {
        return Assembly::create(array_merge([
            'nama_majelis' => 'Majelis Uji',
            'deskripsi' => 'Deskripsi majelis',
            'guru' => 'Guru Uji',
            'alamat' => 'Jalan Uji No. 1',
            'maps' => 'https://maps.example/x',
            'tipe' => 'Majelis',
            'status' => 'Aktif',
            'province_code' => self::PROVINCE,
            'city_code' => self::CITY,
            'district_code' => self::DISTRICT,
            'village_code' => self::VILLAGE,
        ], $attributes));
    }

    protected function makeTeacher(array $attributes = []): Teacher
    {
        $name = $attributes['name'] ?? 'Guru Uji';

        return Teacher::create(array_merge([
            'name' => $name,
            'slug' => Teacher::generateSlug($name),
            'biografi' => 'Biografi',
        ], $attributes));
    }

    protected function makeSchedule(Assembly $assembly, array $attributes = []): Schedule
    {
        return Schedule::create(array_merge([
            'nama_jadwal' => 'Kajian Uji',
            'deskripsi' => 'Deskripsi kajian',
            'assembly_id' => $assembly->id,
            'waktu' => '2026-01-01 19:30:00',
            'hari' => 'Senin',
            'access' => 'Umum',
            'status' => 'Aktif',
            'recurrence_type' => 'weekly',
            'calendar_system' => 'gregorian',
        ], $attributes));
    }

    /**
     * Acara yang sudah disetujui moderator. Acara buatan Super Admin dibuat
     * lewat `makeEvent(['moderated_at' => now()])` tanpa mengubah `status`,
     * meniru perilaku EventController::store().
     */
    protected function makeEvent(array $attributes = []): Event
    {
        return Event::create(array_merge([
            'name' => 'Acara Uji',
            'location' => 'Jalan Uji No. 1',
            'date' => now()->addDays(14),
            'access' => 'Umum',
            'category' => 'Maulid',
            'user_id' => User::factory()->create()->id,
            'province_code' => self::PROVINCE,
            'city_code' => self::CITY,
            'district_code' => self::DISTRICT,
            'village_code' => self::VILLAGE,
        ], $attributes));
    }
}
