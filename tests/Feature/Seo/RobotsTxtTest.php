<?php

namespace Tests\Feature\Seo;

use Tests\TestCase;

/**
 * robots.txt adalah berkas statis yang dilayani web server, bukan route Laravel,
 * jadi isinya diperiksa langsung dari disk.
 */
class RobotsTxtTest extends TestCase
{
    private function robots(): string
    {
        $path = public_path('robots.txt');

        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    /** @test */
    public function robots_menunjuk_ke_sitemap(): void
    {
        $this->assertStringContainsString('Sitemap: https://syaikhuna.id/sitemap.xml', $this->robots());
    }

    /** @test */
    public function robots_menutup_area_privat_dan_admin(): void
    {
        $robots = $this->robots();

        foreach ([
            'Disallow: /admin/',
            'Disallow: /kelola-',
            'Disallow: /kontributor/saya',
            'Disallow: /favorit-saya',
            'Disallow: /pustaka-saya',
            'Disallow: /pengaturan-akun',
            'Disallow: /registrasi-majelis',
        ] as $direktif) {
            $this->assertStringContainsString($direktif, $robots);
        }
    }

    /** @test */
    public function robots_tetap_mengizinkan_halaman_publik(): void
    {
        $robots = $this->robots();

        $this->assertStringContainsString('User-agent: *', $robots);
        $this->assertStringContainsString('Allow: /', $robots);

        // Halaman ber-noindex tidak boleh diblokir di sini: mesin telusur harus
        // dapat merayapinya untuk membaca tag noindex-nya.
        $this->assertStringNotContainsString('Disallow: /kontributor/profil', $robots);
        $this->assertStringNotContainsString('Disallow: /guru', $robots);
        $this->assertStringNotContainsString('Disallow: /majelis', $robots);
    }
}
