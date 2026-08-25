<?php

namespace Tests\Feature\Seo;

use App\Models\Teacher;
use Tests\Feature\PublicPageTestCase;

class PageStructureTest extends PublicPageTestCase
{
    /**
     * @return array<int, string>
     */
    private function halamanDaftarPublik(): array
    {
        return [
            'beranda', 'guru-list', 'majelis-list', 'jadwal-majelis-list', 'event-list',
            'manaqib-list', 'video-list', 'wirid-list', 'pustaka-list',
            'tulisan.list', 'catatan-pengajian.list',
        ];
    }

    private function jumlahH1(string $html): int
    {
        return preg_match_all('/<h1[\s>]/i', $html);
    }

    /** @test */
    public function setiap_halaman_daftar_publik_punya_tepat_satu_h1(): void
    {
        foreach ($this->halamanDaftarPublik() as $routeName) {
            $html = $this->get(route($routeName))->assertOk()->getContent();

            $this->assertSame(1, $this->jumlahH1($html), "Halaman [$routeName] tidak punya tepat satu <h1>.");
        }
    }

    /** @test */
    public function halaman_detail_memakai_nama_entitas_sebagai_satu_satunya_h1(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Struktur', 'foto' => 'guru/large/a.webp']);
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Struktur']);
        $jadwal = $this->makeSchedule($majelis, ['nama_jadwal' => 'Kajian Struktur']);
        $acara = $this->makeEvent(['name' => 'Acara Struktur', 'moderated_at' => now()]);

        $kasus = [
            route('guru-detail', $guru->slug) => 'Guru Struktur',
            route('majelis-detail', $majelis->route_slug) => 'Majelis Struktur',
            route('jadwal-majelis-detail', $jadwal->route_slug) => 'Kajian Struktur',
            route('event-detail', $acara->route_slug) => 'Acara Struktur',
        ];

        foreach ($kasus as $url => $nama) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertSame(1, $this->jumlahH1($html), "Halaman [$url] tidak punya tepat satu <h1>.");
            $this->assertMatchesRegularExpression(
                '/<h1[^>]*>\s*'.preg_quote($nama, '/').'/',
                $html,
                "<h1> pada [$url] bukan nama entitasnya."
            );
        }
    }

    /**
     * Label halaman seperti "Detail Guru" bukan judul konten; menyisakannya
     * sebagai <h1> membuat setiap halaman guru punya judul yang sama.
     *
     * @test
     */
    public function label_halaman_tidak_lagi_menjadi_h1(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Label', 'foto' => 'guru/large/a.webp']);

        $html = $this->get(route('guru-detail', $guru->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Detail Guru', $html);
        $this->assertDoesNotMatchRegularExpression('/<h1[^>]*>\s*Detail Guru/', $html);
    }

    /**
     * `schedules.teacher_id` nullable. Sebelum penjaga null ditambahkan, satu
     * jadwal tanpa guru membuat beranda, /jadwal-majelis, dan detail majelis
     * membalas 500 — perayap membaca situsnya rusak, bukan sekadar kosong.
     *
     * @test
     */
    public function jadwal_tanpa_guru_tidak_meruntuhkan_halaman_publik(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Tanpa Guru']);
        $this->makeSchedule($majelis, ['nama_jadwal' => 'Kajian Tanpa Guru', 'teacher_id' => null]);

        $this->get(route('beranda'))->assertOk();
        $this->get(route('jadwal-majelis-list'))->assertOk();
        $this->get(route('majelis-detail', $majelis->route_slug))->assertOk();
    }

    /** @test */
    public function halaman_publik_tidak_meminta_apa_pun_dari_google_fonts(): void
    {
        foreach (['beranda', 'guru-list', 'event-list'] as $routeName) {
            $html = $this->get(route($routeName))->assertOk()->getContent();

            $this->assertStringNotContainsString('fonts.googleapis.com', $html);
            $this->assertStringNotContainsString('fonts.gstatic.com', $html);
        }
    }

    /** @test */
    public function font_inter_di_preload_dari_domain_sendiri(): void
    {
        $html = $this->get(route('beranda'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<link rel="preload" href="[^"]*\/fonts\/inter-latin\.woff2" as="font" type="font\/woff2" crossorigin>/',
            $html
        );
    }

    /**
     * @font-face menunjuk berkas statis; kalau berkasnya hilang, teks jatuh ke
     * fallback tanpa satu pun test lain yang gagal.
     *
     * @test
     */
    public function berkas_font_tersedia_dan_benar_benar_woff2(): void
    {
        foreach (['inter-latin.woff2', 'inter-latin-ext.woff2'] as $berkas) {
            $path = public_path('fonts/'.$berkas);

            $this->assertFileExists($path);
            $this->assertSame('wOF2', file_get_contents($path, false, null, 0, 4), "[$berkas] bukan WOFF2.");
        }
    }

    /** @test */
    public function gambar_daftar_publik_ditunda_pemuatannya(): void
    {
        $this->makeTeacher(['name' => 'Guru Lazy', 'foto' => 'guru/large/a.webp']);

        $html = $this->get(route('guru-list'))->assertOk()->getContent();

        $this->assertStringContainsString('loading="lazy"', $html);
    }

    /**
     * Poster acara berpotensi menjadi elemen LCP; menunda yang pertama justru
     * memperlambat halaman.
     *
     * @test
     */
    public function poster_acara_pertama_dimuat_eager_sisanya_lazy(): void
    {
        $this->makeEvent(['name' => 'Acara Satu', 'moderated_at' => now(), 'image' => 'events/a.webp']);
        $this->makeEvent(['name' => 'Acara Dua', 'moderated_at' => now(), 'image' => 'events/b.webp']);

        $html = $this->get(route('event-list'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'loading="eager"'));
        $this->assertStringContainsString('loading="lazy"', $html);
    }

    /** @test */
    public function foto_guru_memakai_alt_yang_menyebut_namanya(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Alt', 'foto' => 'guru/large/a.webp']);

        $html = $this->get(route('guru-detail', $guru->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('alt="Avatar"', $html);
        $this->assertStringContainsString('alt="Foto Guru Alt"', $html);
    }

    /**
     * Daftar publik dibatasi 10 per halaman. Yang diuji di sini bukan urutannya
     * (ListGuru memakai latest(), dan created_at bisa seri) melainkan bahwa
     * `?page=2` benar-benar dirender di sisi server sehingga dapat dirayapi.
     *
     * @test
     */
    public function daftar_guru_berhalaman_dan_halaman_kedua_dapat_dirayapi(): void
    {
        $nama = [];

        for ($i = 1; $i <= 12; $i++) {
            $nama[] = sprintf('Guru Halaman %02d', $i);
            $this->makeTeacher(['name' => end($nama), 'foto' => 'guru/large/a.webp']);
        }

        $halamanSatu = $this->get(route('guru-list'))->assertOk()->getContent();
        $halamanDua = $this->get(route('guru-list').'?page=2')->assertOk()->getContent();

        $tampil = fn (string $html) => array_values(array_filter(
            $nama,
            fn (string $n) => str_contains($html, $n)
        ));

        $this->assertCount(10, $tampil($halamanSatu));
        $this->assertCount(2, $tampil($halamanDua));
        $this->assertSame([], array_intersect($tampil($halamanSatu), $tampil($halamanDua)));
    }
}
