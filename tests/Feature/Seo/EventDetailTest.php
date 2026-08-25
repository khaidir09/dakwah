<?php

namespace Tests\Feature\Seo;

use App\Models\Event;
use Tests\Feature\PublicPageTestCase;

class EventDetailTest extends PublicPageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://syaikhuna.id']);
    }

    /**
     * Acara yang sah tayang: sudah dimoderasi, tidak ditolak, dan terbuka untuk umum.
     */
    private function acaraTayang(array $attributes = []): Event
    {
        return $this->makeEvent(array_merge([
            'name' => 'Haul Akbar Sekumpul',
            'moderated_at' => now(),
            'access' => 'Umum',
        ], $attributes));
    }

    /** @test */
    public function acara_yang_sah_tayang_dapat_dibuka_publik(): void
    {
        $acara = $this->acaraTayang();

        $this->get(route('event-detail', $acara->route_slug))
            ->assertOk()
            ->assertSee('Haul Akbar Sekumpul');
    }

    /** @test */
    public function acara_belum_dimoderasi_tidak_dapat_dibuka_publik(): void
    {
        $acara = $this->makeEvent(['access' => 'Umum']);

        $this->get(route('event-detail', $acara->route_slug))->assertNotFound();
    }

    /** @test */
    public function acara_ditolak_tidak_dapat_dibuka_publik(): void
    {
        $acara = $this->acaraTayang(['status' => 'rejected']);

        $this->get(route('event-detail', $acara->route_slug))->assertNotFound();
    }

    /**
     * `access = 'Khusus'` adalah keputusan produk, bukan moderasi: acaranya tetap
     * tampil di daftar, tetapi tidak mendapat halaman yang dapat diindeks.
     *
     * @test
     */
    public function acara_khusus_tidak_punya_halaman_detail_publik(): void
    {
        $acara = $this->acaraTayang(['access' => 'Khusus']);

        $this->get(route('event-detail', $acara->route_slug))->assertNotFound();
    }

    /**
     * Acara buatan Super Admin hanya mengisi `moderated_at` dan membiarkan
     * `status` tetap 'pending' — regresi yang sama dengan K7.
     *
     * @test
     */
    public function acara_buatan_super_admin_tetap_dapat_dibuka(): void
    {
        $acara = $this->makeEvent([
            'moderated_at' => now(),
            'access' => 'Umum',
            'status' => 'pending',
        ]);

        $this->get(route('event-detail', $acara->route_slug))->assertOk();
    }

    /** @test */
    public function pemilik_dapat_melihat_pratinjau_acaranya_sendiri(): void
    {
        $pemilik = $this->kontributor();
        $acara = $this->makeEvent(['user_id' => $pemilik->id, 'access' => 'Umum']);

        $this->actingAs($pemilik)
            ->get(route('event-detail', $acara->route_slug))
            ->assertOk()
            ->assertSee('belum tayang untuk umum');
    }

    /** @test */
    public function super_admin_dapat_melihat_acara_belum_dimoderasi(): void
    {
        $acara = $this->makeEvent(['access' => 'Umum']);

        $this->actingAs($this->superAdmin())
            ->get(route('event-detail', $acara->route_slug))
            ->assertOk();
    }

    /** @test */
    public function kontributor_lain_tidak_dapat_melihat_acara_belum_dimoderasi(): void
    {
        $acara = $this->makeEvent(['user_id' => $this->kontributor()->id, 'access' => 'Umum']);

        $this->actingAs($this->kontributor())
            ->get(route('event-detail', $acara->route_slug))
            ->assertNotFound();
    }

    /** @test */
    public function url_hanya_id_dialihkan_permanen_ke_bentuk_kanonik(): void
    {
        $acara = $this->acaraTayang();

        $this->get('/event/'.$acara->id)
            ->assertStatus(301)
            ->assertRedirect(route('event-detail', $acara->route_slug));
    }

    /** @test */
    public function slug_keliru_dialihkan_permanen_ke_bentuk_kanonik(): void
    {
        $acara = $this->acaraTayang();

        $this->get('/event/'.$acara->id.'-slug-yang-salah')
            ->assertStatus(301)
            ->assertRedirect(route('event-detail', $acara->route_slug));
    }

    /** @test */
    public function bentuk_kanonik_tidak_memicu_redirect(): void
    {
        $acara = $this->acaraTayang();

        $this->get(route('event-detail', $acara->route_slug))->assertOk();
    }

    /** @test */
    public function parameter_non_numerik_menghasilkan_404(): void
    {
        $this->get('/event/haul-akbar')->assertNotFound();
    }

    /**
     * Kanonikalisasi tidak boleh mendahului cek visibilitas: header Location-nya
     * akan membocorkan nama acara yang belum disetujui.
     *
     * @test
     */
    public function redirect_tidak_membocorkan_acara_yang_belum_disetujui(): void
    {
        $acara = $this->makeEvent(['name' => 'Rapat Internal Panitia', 'access' => 'Umum']);

        $response = $this->get('/event/'.$acara->id);

        $response->assertNotFound();
        $this->assertNull($response->headers->get('Location'));
    }

    /** @test */
    public function halaman_detail_memakai_nama_acara_sebagai_judul_dan_canonical(): void
    {
        $acara = $this->acaraTayang();

        $html = $this->get(route('event-detail', $acara->route_slug))->assertOk()->getContent();

        $this->assertStringContainsString('<title>Haul Akbar Sekumpul — Syaikhuna</title>', $html);
        $this->assertStringContainsString(
            '<link rel="canonical" href="https://syaikhuna.id/event/'.$acara->route_slug.'"',
            $html
        );
    }

    /** @test */
    public function daftar_acara_menautkan_acara_tayang_dan_tidak_menautkan_acara_khusus(): void
    {
        $umum = $this->acaraTayang(['name' => 'Maulid Umum']);
        $khusus = $this->acaraTayang(['name' => 'Maulid Khusus', 'access' => 'Khusus']);

        $html = $this->get(route('event-list'))->assertOk()->getContent();

        $this->assertStringContainsString('/event/'.$umum->route_slug, $html);
        $this->assertStringNotContainsString('/event/'.$khusus->route_slug, $html);
    }
}
