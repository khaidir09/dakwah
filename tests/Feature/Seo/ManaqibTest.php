<?php

namespace Tests\Feature\Seo;

use App\Models\Teacher;
use Tests\Feature\PublicPageTestCase;

class ManaqibTest extends PublicPageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://syaikhuna.id']);
    }

    private function canonical(string $html): ?string
    {
        preg_match('/<link rel="canonical" href="([^"]+)"/', $html, $m);

        return $m[1] ?? null;
    }

    /** @test */
    public function selama_manaqib_kosong_halaman_manaqib_canonical_ke_halaman_guru(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Tanpa Manaqib', 'manaqib' => null]);

        $html = $this->get(route('manaqib-detail', $guru->slug))->assertOk()->getContent();

        $this->assertSame('https://syaikhuna.id/guru/'.$guru->slug, $this->canonical($html));
    }

    /** @test */
    public function manaqib_terisi_canonical_ke_dirinya_sendiri(): void
    {
        $guru = $this->makeTeacher([
            'name' => 'Guru Bermanaqib',
            'manaqib' => '<p>Riwayat hidup lengkap beserta sanad keilmuannya.</p>',
        ]);

        $html = $this->get(route('manaqib-detail', $guru->slug))->assertOk()->getContent();

        $this->assertSame('https://syaikhuna.id/manaqib/'.$guru->slug, $this->canonical($html));
    }

    /**
     * Editor WYSIWYG menyimpan paragraf kosong saat kolom dikosongkan lagi. Kalau
     * itu dianggap terisi, halaman kosong ikut mengaku kanonik dan masuk sitemap
     * sebagai duplikat.
     *
     * @test
     */
    public function manaqib_yang_hanya_berisi_markup_kosong_tetap_dianggap_kosong(): void
    {
        $kasus = ['<p></p>', '<p>&nbsp;</p>', '   ', '<p> <br> </p>'];

        foreach ($kasus as $index => $isi) {
            $guru = $this->makeTeacher(['name' => 'Guru Kosong '.$index, 'manaqib' => $isi]);

            $this->assertFalse($guru->hasManaqib(), "Manaqib kasus [$index] seharusnya dianggap kosong.");

            $html = $this->get(route('manaqib-detail', $guru->slug))->assertOk()->getContent();

            $this->assertSame('https://syaikhuna.id/guru/'.$guru->slug, $this->canonical($html));
        }
    }

    /** @test */
    public function halaman_manaqib_menampilkan_biografi_selama_manaqib_kosong(): void
    {
        $guru = $this->makeTeacher([
            'name' => 'Guru Biografi Saja',
            'biografi' => '<p>Isi biografi ringkas.</p>',
            'manaqib' => null,
        ]);

        $this->get(route('manaqib-detail', $guru->slug))
            ->assertOk()
            ->assertSee('Isi biografi ringkas.');
    }

    /** @test */
    public function halaman_manaqib_menampilkan_manaqib_dan_bukan_biografi_saat_terisi(): void
    {
        $guru = $this->makeTeacher([
            'name' => 'Guru Dua Konten',
            'biografi' => '<p>Isi biografi ringkas.</p>',
            'manaqib' => '<p>Isi manaqib mendalam.</p>',
        ]);

        $html = $this->get(route('manaqib-detail', $guru->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Isi manaqib mendalam.', $html);
        $this->assertStringNotContainsString('Isi biografi ringkas.', $html);
    }

    /** @test */
    public function halaman_guru_tidak_ikut_berubah_saat_manaqib_terisi(): void
    {
        $guru = $this->makeTeacher([
            'name' => 'Guru Tetap',
            'biografi' => '<p>Isi biografi ringkas.</p>',
            'manaqib' => '<p>Isi manaqib mendalam.</p>',
        ]);

        $html = $this->get(route('guru-detail', $guru->slug))->assertOk()->getContent();

        $this->assertSame('https://syaikhuna.id/guru/'.$guru->slug, $this->canonical($html));
        $this->assertStringContainsString('Isi biografi ringkas.', $html);
        $this->assertStringNotContainsString('Isi manaqib mendalam.', $html);
    }

    /** @test */
    public function hanya_manaqib_terisi_yang_masuk_sitemap(): void
    {
        $kosong = $this->makeTeacher(['name' => 'Guru Kosong Sitemap', 'manaqib' => null]);
        $terisi = $this->makeTeacher(['name' => 'Guru Terisi Sitemap', 'manaqib' => '<p>Manaqib panjang.</p>']);

        $xml = $this->get(route('sitemap'))->assertOk()->getContent();

        $this->assertStringContainsString('<loc>https://syaikhuna.id/guru/'.$kosong->slug.'</loc>', $xml);
        $this->assertStringNotContainsString('<loc>https://syaikhuna.id/manaqib/'.$kosong->slug.'</loc>', $xml);

        $this->assertStringContainsString('<loc>https://syaikhuna.id/guru/'.$terisi->slug.'</loc>', $xml);
        $this->assertStringContainsString('<loc>https://syaikhuna.id/manaqib/'.$terisi->slug.'</loc>', $xml);
    }

    /** @test */
    public function guru_belum_dimoderasi_tidak_membawa_manaqibnya_ke_sitemap(): void
    {
        $guru = $this->makeTeacher([
            'name' => 'Guru Pending Manaqib',
            'manaqib' => '<p>Manaqib panjang.</p>',
            'contribution_status' => 'pending',
        ]);

        $xml = $this->get(route('sitemap'))->assertOk()->getContent();

        $this->assertStringNotContainsString('/manaqib/'.$guru->slug, $xml);
        $this->assertStringNotContainsString('/guru/'.$guru->slug, $xml);
    }

    /**
     * Selama halaman ini kanonik ke `/guru/{slug}`, menerbitkan Person di sini
     * berarti dua entitas untuk satu ulama — persis duplikasi yang hendak
     * diselesaikan.
     *
     * @test
     */
    public function person_jsonld_hanya_terbit_saat_manaqib_terisi(): void
    {
        $kosong = $this->makeTeacher(['name' => 'Guru Kosong Jsonld', 'manaqib' => null]);
        $terisi = $this->makeTeacher(['name' => 'Guru Terisi Jsonld', 'manaqib' => '<p>Manaqib panjang.</p>']);

        $this->assertNull($this->person($this->get(route('manaqib-detail', $kosong->slug))->getContent()));

        $person = $this->person($this->get(route('manaqib-detail', $terisi->slug))->getContent());

        $this->assertNotNull($person);
        $this->assertSame('Guru Terisi Jsonld', $person['name']);
        $this->assertSame('https://syaikhuna.id/manaqib/'.$terisi->slug, $person['url']);
    }

    /** @test */
    public function admin_menyimpan_manaqib_yang_sudah_dibersihkan(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('guru.store'), [
                'name' => 'Guru Baru Admin',
                'biografi' => '<p>Biografi.</p>',
                'manaqib' => '<p>Manaqib aman.</p><script>alert(1)</script>',
            ])
            ->assertRedirect();

        $guru = Teacher::where('name', 'Guru Baru Admin')->firstOrFail();

        $this->assertStringContainsString('Manaqib aman.', $guru->manaqib);
        $this->assertStringNotContainsString('<script>', $guru->manaqib);
        $this->assertTrue($guru->hasManaqib());
    }

    /** @test */
    public function format_editor_lengkap_bertahan_saat_manaqib_disimpan(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('guru.store'), [
                'name' => 'Guru Manaqib Berformat',
                'biografi' => '<p>Biografi.</p>',
                'manaqib' => '<h2 style="text-align: center">Sanad Keilmuan</h2>'
                    .'<blockquote><p>Kutipan nasihat.</p></blockquote><hr>'
                    .'<p><s>coret</s> <mark style="background-color: #ffc078">stabilo</mark> <code>kode</code></p>'
                    .'<div data-youtube-video=""><iframe src="https://www.youtube.com/embed/KaLxCiilHns"></iframe></div>'
                    .'<iframe src="https://evil.example/embed/x"></iframe>'
                    .'<p onclick="alert(1)" style="position: fixed">z</p><script>alert(1)</script>',
            ])
            ->assertRedirect();

        $manaqib = Teacher::where('name', 'Guru Manaqib Berformat')->firstOrFail()->manaqib;

        $this->assertStringContainsString('<h2 style="text-align:center;">Sanad Keilmuan</h2>', $manaqib);
        $this->assertStringContainsString('<blockquote>', $manaqib);
        $this->assertStringContainsString('<hr', $manaqib);
        $this->assertStringContainsString('<s>coret</s>', $manaqib);
        $this->assertStringContainsString('<mark style="background-color:#ffc078;">stabilo</mark>', $manaqib);
        $this->assertStringContainsString('<code>kode</code>', $manaqib);
        $this->assertStringContainsString('src="https://www.youtube.com/embed/KaLxCiilHns"', $manaqib);

        $this->assertStringNotContainsString('evil.example', $manaqib);
        $this->assertStringNotContainsString('onclick', $manaqib);
        $this->assertStringNotContainsString('position', $manaqib);
        $this->assertStringNotContainsString('<script>', $manaqib);
    }

    /** @test */
    public function heading_manaqib_tampil_di_halaman_publik(): void
    {
        $guru = $this->makeTeacher([
            'name' => 'Guru Heading Manaqib',
            'manaqib' => '<h2>Guru-guru Beliau</h2><blockquote><p>Nasihat beliau.</p></blockquote>',
        ]);

        $this->get(route('manaqib-detail', $guru->slug))
            ->assertOk()
            ->assertSee('<h2>Guru-guru Beliau</h2>', false)
            ->assertSee('<blockquote>', false);
    }

    /** @test */
    public function manaqib_boleh_dikosongkan_admin(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('guru.store'), [
                'name' => 'Guru Tanpa Manaqib Admin',
                'biografi' => '<p>Biografi.</p>',
            ])
            ->assertRedirect();

        $guru = Teacher::where('name', 'Guru Tanpa Manaqib Admin')->firstOrFail();

        $this->assertNull($guru->manaqib);
        $this->assertFalse($guru->hasManaqib());
    }

    // ---------------------------------------------------------------- helpers

    /**
     * @return array<string, mixed>|null
     */
    private function person(string $html): ?array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);

        foreach ($m[1] as $json) {
            $data = json_decode($json, true);

            if (($data['@type'] ?? null) === 'Person') {
                return $data;
            }
        }

        return null;
    }
}
