<?php

namespace Tests\Feature\Seo;

use App\Models\Assembly;
use App\Models\Schedule;
use Tests\Feature\PublicPageTestCase;

class CanonicalUrlTest extends PublicPageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://syaikhuna.id']);
    }

    // ------------------------------------------------------------- beranda

    /** @test */
    public function root_menyajikan_beranda_langsung(): void
    {
        $this->get('/')->assertOk();
    }

    /** @test */
    public function beranda_lama_diarahkan_permanen_ke_root(): void
    {
        $this->get('/beranda')
            ->assertStatus(301)
            ->assertRedirect('/');
    }

    /** @test */
    public function route_beranda_menghasilkan_root(): void
    {
        $this->assertSame(url('/'), route('beranda'));
    }

    // --------------------------------------------------- slug dibuat otomatis

    /** @test */
    public function slug_majelis_dibuat_otomatis_saat_disimpan(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Ar-Raudhah Sekumpul']);

        $this->assertSame('majelis-ar-raudhah-sekumpul', $majelis->slug);
        $this->assertSame($majelis->id.'-majelis-ar-raudhah-sekumpul', $majelis->route_slug);
    }

    /** @test */
    public function slug_jadwal_dibuat_otomatis_saat_disimpan(): void
    {
        $jadwal = $this->makeSchedule($this->makeAssembly(), ['nama_jadwal' => 'Kajian Sabilal Muhtadin']);

        $this->assertSame('kajian-sabilal-muhtadin', $jadwal->slug);
    }

    /** @test */
    public function dua_majelis_bernama_sama_boleh_berbagi_slug(): void
    {
        $a = $this->makeAssembly(['nama_majelis' => 'Majelis Nurul Iman']);
        $b = $this->makeAssembly(['nama_majelis' => 'Majelis Nurul Iman']);

        $this->assertSame($a->slug, $b->slug);
        $this->assertNotSame($a->route_slug, $b->route_slug);
    }

    /** @test */
    public function nama_arab_tetap_menghasilkan_slug_via_transliterasi(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'مجلس']);

        $this->assertSame('mgls', $majelis->slug);
    }

    /** @test */
    public function nama_tanpa_karakter_yang_bisa_dislugkan_menghasilkan_slug_null(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => '🕌 🕌']);

        $this->assertNull($majelis->slug);
        $this->assertSame((string) $majelis->id, $majelis->route_slug);
        $this->assertStringEndsNotWith('-', $majelis->route_slug);
    }

    // ------------------------------------------------------- kanonikalisasi

    /** @test */
    public function url_id_saja_diarahkan_permanen_ke_bentuk_kanonik(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Ar-Raudhah']);

        $this->get('/majelis/'.$majelis->id)
            ->assertStatus(301)
            ->assertRedirect(route('majelis-detail', $majelis->route_slug));
    }

    /** @test */
    public function url_kanonik_dilayani_tanpa_redirect(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Ar-Raudhah']);

        $this->get(route('majelis-detail', $majelis->route_slug))->assertOk();
    }

    /** @test */
    public function slug_ngawur_diarahkan_ke_bentuk_kanonik_bukan_404(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Ar-Raudhah']);

        $this->get('/majelis/'.$majelis->id.'-slug-karangan-orang')
            ->assertStatus(301)
            ->assertRedirect(route('majelis-detail', $majelis->route_slug));
    }

    /** @test */
    public function parameter_bukan_angka_menghasilkan_404(): void
    {
        $this->get('/majelis/abc')->assertNotFound();
        $this->get('/jadwal-majelis/abc')->assertNotFound();
    }

    /** @test */
    public function majelis_tanpa_slug_dilayani_di_url_id_saja(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => '🕌 🕌']);

        $this->get('/majelis/'.$majelis->id)->assertOk();
    }

    /** @test */
    public function ganti_nama_majelis_memperbarui_slug_dan_url_lama_tetap_hidup(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Nama Lama']);
        $urlLama = $majelis->route_slug;

        $majelis->update(['nama_majelis' => 'Majelis Nama Baru']);
        $majelis->refresh();

        $this->assertSame('majelis-nama-baru', $majelis->slug);
        $this->assertNotSame($urlLama, $majelis->route_slug);

        $this->get('/majelis/'.$urlLama)
            ->assertStatus(301)
            ->assertRedirect(route('majelis-detail', $majelis->route_slug));
    }

    /** @test */
    public function jadwal_ikut_aturan_kanonikalisasi_yang_sama(): void
    {
        $majelis = $this->makeAssembly();
        $guru = $this->makeTeacher(['foto' => 'guru/uji.webp']);
        $jadwal = $this->makeSchedule($majelis, [
            'nama_jadwal' => 'Kajian Kitab Sifat Dua Puluh',
            'teacher_id' => $guru->id,
        ]);

        $this->get('/jadwal-majelis/'.$jadwal->id)
            ->assertStatus(301)
            ->assertRedirect(route('jadwal-majelis-detail', $jadwal->route_slug));

        $this->get(route('jadwal-majelis-detail', $jadwal->route_slug))->assertOk();
    }

    // ------------------------------------------------------------ keamanan

    /** @test */
    public function majelis_pending_tetap_404_dan_tidak_membocorkan_slug_lewat_redirect(): void
    {
        $majelis = $this->makeAssembly([
            'nama_majelis' => 'Majelis Rahasia Belum Disetujui',
            'contribution_status' => 'pending',
            'user_id' => $this->kontributor()->id,
        ]);

        $response = $this->get('/majelis/'.$majelis->id);

        $response->assertNotFound();
        $this->assertNull($response->headers->get('Location'));
    }

    /** @test */
    public function jadwal_pending_tetap_404_dan_tidak_membocorkan_slug_lewat_redirect(): void
    {
        $jadwal = $this->makeSchedule($this->makeAssembly(), [
            'nama_jadwal' => 'Kajian Rahasia Belum Disetujui',
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $response = $this->get('/jadwal-majelis/'.$jadwal->id);

        $response->assertNotFound();
        $this->assertNull($response->headers->get('Location'));
    }

    // ------------------------------------------------------------ canonical

    /** @test */
    public function canonical_halaman_detail_memakai_url_berslug(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Ar-Raudhah']);

        $html = $this->get(route('majelis-detail', $majelis->route_slug))->assertOk()->getContent();

        preg_match('/<link rel="canonical" href="([^"]*)"/', $html, $m);

        $this->assertSame('https://syaikhuna.id/majelis/'.$majelis->route_slug, $m[1] ?? null);
    }

    /** @test */
    public function tautan_internal_memakai_bentuk_berslug(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Ar-Raudhah']);

        $this->get(route('majelis-list'))
            ->assertOk()
            ->assertSee('/majelis/'.$majelis->route_slug, false);
    }

    // ------------------------------------------------------------- backfill

    /** @test */
    public function backfill_mengisi_slug_baris_lama(): void
    {
        // Tiru baris pra-migrasi: slug dikosongkan lewat query builder agar
        // event `saving` tidak ikut memulihkannya.
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Tanpa Slug']);
        Assembly::whereKey($majelis->id)->update(['slug' => null]);

        $jadwal = $this->makeSchedule($majelis, ['nama_jadwal' => 'Kajian Tanpa Slug']);
        Schedule::whereKey($jadwal->id)->update(['slug' => null]);

        require_once database_path('migrations/2026_08_05_000002_backfill_route_slugs.php');
        (include database_path('migrations/2026_08_05_000002_backfill_route_slugs.php'))->up();

        $this->assertSame('majelis-tanpa-slug', $majelis->fresh()->slug);
        $this->assertSame('kajian-tanpa-slug', $jadwal->fresh()->slug);
    }
}
