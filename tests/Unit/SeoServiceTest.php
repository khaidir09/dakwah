<?php

namespace Tests\Unit;

use App\Services\SeoService;
use Tests\TestCase;

/**
 * SeoService memakai config(), request(), dan public_path(), jadi butuh
 * container Laravel — bukan PHPUnit\Framework\TestCase polos.
 */
class SeoServiceTest extends TestCase
{
    private SeoService $seo;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://syaikhuna.id',
            'app.name' => 'Syaikhuna',
        ]);

        $this->seo = new SeoService;
    }

    /** @test */
    public function description_membuang_tag_html(): void
    {
        $hasil = $this->seo->description('<p>Beliau <strong>ulama</strong> Banjar.</p>');

        $this->assertSame('Beliau ulama Banjar.', $hasil);
    }

    /** @test */
    public function description_membuang_script_sepenuhnya(): void
    {
        $hasil = $this->seo->description('Halo<script>alert(1)</script> dunia');

        $this->assertStringNotContainsString('<', $hasil);
        $this->assertStringNotContainsString('script', $hasil);
    }

    /** @test */
    public function description_mendecode_entitas_html(): void
    {
        $hasil = $this->seo->description('Guru&nbsp;Sekumpul &amp; murid');

        $this->assertSame('Guru Sekumpul & murid', $hasil);
    }

    /** @test */
    public function description_merapatkan_whitespace_ganda(): void
    {
        $hasil = $this->seo->description("Baris satu\n\n   Baris   dua\t");

        $this->assertSame('Baris satu Baris dua', $hasil);
    }

    /** @test */
    public function description_dibatasi_155_karakter(): void
    {
        $hasil = $this->seo->description(str_repeat('a', 400));

        $this->assertLessThanOrEqual(SeoService::DESCRIPTION_LIMIT + 1, mb_strlen($hasil));
    }

    /** @test */
    public function description_memakai_fallback_saat_kosong(): void
    {
        $this->assertSame('Cadangan', $this->seo->description(null, 'Cadangan'));
        $this->assertSame('Cadangan', $this->seo->description('', 'Cadangan'));
        $this->assertSame('Cadangan', $this->seo->description('   ', 'Cadangan'));
        $this->assertSame('Cadangan', $this->seo->description('<p></p>', 'Cadangan'));
    }

    /** @test */
    public function description_tidak_pernah_kosong(): void
    {
        $this->assertNotSame('', $this->seo->description(null));
    }

    /** @test */
    public function title_menambahkan_nama_situs(): void
    {
        $this->assertSame('Guru Sekumpul — Syaikhuna', $this->seo->title('Guru Sekumpul'));
    }

    /** @test */
    public function title_tanpa_argumen_mengembalikan_nama_situs_saja(): void
    {
        $this->assertSame('Syaikhuna', $this->seo->title(null));
        $this->assertSame('Syaikhuna', $this->seo->title('  '));
    }

    /** @test */
    public function title_dibatasi_agar_tidak_terpotong_di_serp(): void
    {
        $hasil = $this->seo->title(str_repeat('panjang ', 30));

        $this->assertLessThanOrEqual(SeoService::TITLE_LIMIT + 1, mb_strlen($hasil));
        $this->assertStringEndsWith('— Syaikhuna', $hasil);
    }

    /** @test */
    public function canonical_absolut_dari_config_bukan_host_request(): void
    {
        $this->assertSame('https://syaikhuna.id/guru/abah-guru-sekumpul', $this->seo->canonical('guru/abah-guru-sekumpul'));
        $this->assertSame('https://syaikhuna.id/guru/abah-guru-sekumpul', $this->seo->canonical('/guru/abah-guru-sekumpul/'));
    }

    /** @test */
    public function canonical_root_memakai_garis_miring_tunggal(): void
    {
        $this->assertSame('https://syaikhuna.id/', $this->seo->canonical('/'));
    }

    /** @test */
    public function canonical_mengabaikan_query_string_dari_request(): void
    {
        $this->get('/guru?search=zaini&page=3');

        $this->assertSame('https://syaikhuna.id/guru', $this->seo->canonical());
    }

    /** @test */
    public function image_memakai_gambar_entitas_bila_ada(): void
    {
        $hasil = $this->seo->image('guru/large/foto.webp', 'guru');

        $this->assertStringStartsWith('https://syaikhuna.id/', $hasil);
        $this->assertStringContainsString('guru/large/foto.webp', $hasil);
    }

    /** @test */
    public function image_jatuh_ke_logo_saat_banner_kategori_belum_ada(): void
    {
        $hasil = $this->seo->image(null, 'kategori-yang-tidak-punya-banner');

        $this->assertSame('https://syaikhuna.id/images/android-chrome-512x512.png', $hasil);
    }

    /** @test */
    public function image_selalu_absolut(): void
    {
        foreach ([null, '', 'majelis/large/x.webp'] as $input) {
            $this->assertStringStartsWith('https://', $this->seo->image($input, 'majelis'));
        }
    }
}
