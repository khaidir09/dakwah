<?php

namespace Tests\Feature\Api;

use App\Models\Wirid;
use Illuminate\Support\Facades\DB;

/**
 * Test regresi keamanan: memastikan konten non-publik tidak pernah keluar lewat API,
 * terlepas dari apa yang ditampilkan halaman web.
 */
class ApiDataLeakTest extends ApiTestCase
{
    private function scheduleIds(string $uri): array
    {
        return array_column($this->apiGet($uri)->assertOk()->json('data'), 'schedule_id');
    }

    private function jadwalUri(): string
    {
        return '/api/v1/jadwal-majelis?city_code='.self::CITY.'&days=30';
    }

    /** @test */
    public function pending_and_rejected_schedules_are_hidden()
    {
        $assembly = $this->makeAssembly();
        $visible = $this->makeSchedule($assembly);
        $pending = $this->makeSchedule($assembly, ['contribution_status' => 'pending']);
        $rejected = $this->makeSchedule($assembly, ['contribution_status' => 'rejected']);
        $approved = $this->makeSchedule($assembly, ['contribution_status' => 'approved']);

        $ids = $this->scheduleIds($this->jadwalUri());

        $this->assertContains($visible->id, $ids);
        $this->assertContains($approved->id, $ids);
        $this->assertNotContains($pending->id, $ids);
        $this->assertNotContains($rejected->id, $ids);
    }

    /** @test */
    public function access_is_an_allowlist_not_a_blocklist()
    {
        $assembly = $this->makeAssembly();

        $allowed = collect(['Umum', 'Ikhwan', 'Akhwat'])
            ->map(fn ($access) => $this->makeSchedule($assembly, ['access' => $access]));

        $khusus = $this->makeSchedule($assembly, ['access' => 'Khusus']);

        // Nilai yang belum dikenal harus ikut tersembunyi tanpa perubahan kode.
        $unexpected = $this->makeSchedule($assembly, ['access' => 'Internal Pengurus']);

        $ids = $this->scheduleIds($this->jadwalUri());

        foreach ($allowed as $schedule) {
            $this->assertContains($schedule->id, $ids);
        }

        $this->assertNotContains($khusus->id, $ids);
        $this->assertNotContains($unexpected->id, $ids);
    }

    /** @test */
    public function only_active_schedules_are_returned()
    {
        $assembly = $this->makeAssembly();
        $aktif = $this->makeSchedule($assembly);
        $hidden = collect(['Selesai', 'Batal', 'Libur Ramadhan'])
            ->map(fn ($status) => $this->makeSchedule($assembly, ['status' => $status]));

        $ids = $this->scheduleIds($this->jadwalUri());

        $this->assertContains($aktif->id, $ids);

        foreach ($hidden as $schedule) {
            $this->assertNotContains($schedule->id, $ids);
        }
    }

    /** @test */
    public function schedules_of_a_non_public_assembly_are_hidden()
    {
        $hiddenAssembly = $this->makeAssembly(['contribution_status' => 'pending']);
        $schedule = $this->makeSchedule($hiddenAssembly);

        $this->assertNotContains($schedule->id, $this->scheduleIds($this->jadwalUri()));
    }

    /** @test */
    public function the_schedule_payload_does_not_expose_internal_fields()
    {
        $this->makeSchedule($this->makeAssembly(), [
            'deskripsi' => '<p>catatan internal</p>',
            'rejection_reason' => 'alasan internal',
        ]);

        $response = $this->apiGet($this->jadwalUri())->assertOk();

        foreach (['deskripsi', 'contributor_user_id', 'rejection_reason', 'user_id', 'contribution_status'] as $field) {
            $this->assertArrayNotHasKey($field, $response->json('data.0'));
        }

        $response->assertDontSee('catatan internal')->assertDontSee('alasan internal');
    }

    /** @test */
    public function pending_and_rejected_wirids_are_hidden()
    {
        $base = [
            'kategori' => 'wirid',
            'nama' => 'Wirid',
            'arab' => 'نص',
        ];

        $visible = Wirid::create($base + ['nama' => 'Tampil']);
        $pending = Wirid::create($base + ['nama' => 'Pending', 'contribution_status' => 'pending']);
        $rejected = Wirid::create($base + ['nama' => 'Ditolak', 'contribution_status' => 'rejected']);

        $ids = array_column($this->apiGet('/api/v1/wirid')->assertOk()->json('data'), 'id');

        $this->assertContains($visible->id, $ids);
        $this->assertNotContains($pending->id, $ids);
        $this->assertNotContains($rejected->id, $ids);
    }

    /** @test */
    public function pending_and_rejected_teachers_are_hidden_from_haul()
    {
        $wafat = ['wafat_hijriah_day' => 1, 'wafat_hijriah_month' => 3, 'wafat_hijriah_year' => 1400];

        $visible = $this->makeTeacher(['name' => 'Guru Tampil'] + $wafat);
        $pending = $this->makeTeacher(['name' => 'Guru Pending', 'contribution_status' => 'pending'] + $wafat);
        $rejected = $this->makeTeacher(['name' => 'Guru Ditolak', 'contribution_status' => 'rejected'] + $wafat);

        $ids = array_column($this->apiGet('/api/v1/haul')->assertOk()->json('data'), 'id');

        $this->assertContains($visible->id, $ids);
        $this->assertNotContains($pending->id, $ids);
        $this->assertNotContains($rejected->id, $ids);
    }

    /** @test */
    public function the_cache_key_does_not_bleed_between_different_cities()
    {
        DB::table('indonesia_cities')->insert([
            'code' => '6372', 'province_code' => self::PROVINCE, 'name' => 'Kota Banjarbaru',
        ]);

        $schedule = $this->makeSchedule($this->makeAssembly());

        $this->assertContains($schedule->id, $this->scheduleIds($this->jadwalUri()));
        $this->assertNotContains(
            $schedule->id,
            $this->scheduleIds('/api/v1/jadwal-majelis?city_code=6372&days=30')
        );
    }
}
