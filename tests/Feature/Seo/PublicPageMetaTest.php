<?php

namespace Tests\Feature\Seo;

use App\Models\Post;
use App\Services\SeoService;
use Tests\Feature\PublicPageTestCase;

class PublicPageMetaTest extends PublicPageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Canonical selalu dibangun dari config, bukan host request.
        config(['app.url' => 'https://syaikhuna.id']);
    }

    /**
     * Halaman daftar publik yang bisa dirender tanpa fixture tambahan.
     *
     * @return array<string, array{0: string}>
     */
    public static function halamanDaftar(): array
    {
        return [
            'beranda' => ['beranda'],
            'daftar majelis' => ['majelis-list'],
            'daftar jadwal' => ['jadwal-majelis-list'],
            'daftar guru' => ['guru-list'],
            'daftar video' => ['video-list'],
            'daftar acara' => ['event-list'],
            'daftar amalan' => ['wirid-list'],
            'daftar manaqib' => ['manaqib-list'],
            'daftar pustaka' => ['pustaka-list'],
            'daftar tulisan' => ['tulisan.list'],
            'daftar catatan' => ['catatan-pengajian.list'],
            'daftar ramadhan' => ['ramadhan-list'],
            'tentang kami' => ['tentang-kami'],
            'kontributor' => ['kontributor.index'],
        ];
    }

    /**
     * @dataProvider halamanDaftar
     *
     * @test
     */
    public function halaman_daftar_punya_metadata_lengkap(string $routeName): void
    {
        $html = $this->get(route($routeName))->assertOk()->getContent();

        $this->assertMetadataSehat($html, $routeName);
    }

    /** @test */
    public function judul_setiap_halaman_daftar_berbeda_satu_sama_lain(): void
    {
        $judul = [];

        foreach (array_keys(self::halamanDaftar()) as $label) {
            $routeName = self::halamanDaftar()[$label][0];
            $judul[$routeName] = $this->judul($this->get(route($routeName))->getContent());
        }

        $this->assertSame(
            count($judul),
            count(array_unique($judul)),
            'Ada halaman daftar yang berbagi <title> yang sama: '.json_encode($judul, JSON_UNESCAPED_UNICODE)
        );
    }

    /** @test */
    public function detail_guru_memakai_nama_guru_sebagai_judul(): void
    {
        $guru = $this->makeTeacher([
            'name' => 'Abah Guru Sekumpul',
            'biografi' => '<p>Beliau adalah <strong>ulama besar</strong> asal Martapura.</p>',
            'foto' => 'guru/large/sekumpul.webp',
        ]);

        $html = $this->get(route('guru-detail', $guru->slug))->assertOk()->getContent();

        $this->assertSame('Abah Guru Sekumpul — Syaikhuna', $this->judul($html));
        $this->assertSame('Beliau adalah ulama besar asal Martapura.', $this->meta($html, 'description'));
        $this->assertSame('https://syaikhuna.id/guru/'.$guru->slug, $this->canonical($html));
        $this->assertStringContainsString('guru/large/sekumpul.webp', $this->ogImage($html));
        $this->assertMetadataSehat($html, 'guru-detail');
    }

    /** @test */
    public function detail_guru_tanpa_biografi_memakai_deskripsi_cadangan(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Tanpa Bio', 'biografi' => '']);

        $html = $this->get(route('guru-detail', $guru->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Guru Tanpa Bio', $this->meta($html, 'description'));
    }

    /** @test */
    public function detail_guru_tanpa_foto_jatuh_ke_gambar_cadangan(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Tanpa Foto', 'foto' => null]);

        $html = $this->get(route('guru-detail', $guru->slug))->assertOk()->getContent();

        $this->assertStringStartsWith('https://syaikhuna.id/', $this->ogImage($html));
    }

    /** @test */
    public function detail_majelis_memakai_nama_majelis_sebagai_judul(): void
    {
        $majelis = $this->makeAssembly([
            'nama_majelis' => 'Majelis Ar-Raudhah',
            'deskripsi' => 'Majelis rutin di Sekumpul.',
        ]);

        $html = $this->get(route('majelis-detail', $majelis->route_slug))->assertOk()->getContent();

        $this->assertSame('Majelis Ar-Raudhah — Syaikhuna', $this->judul($html));
        $this->assertSame('Majelis rutin di Sekumpul.', $this->meta($html, 'description'));
        $this->assertSame('https://syaikhuna.id/majelis/'.$majelis->route_slug, $this->canonical($html));
    }

    /** @test */
    public function detail_jadwal_memakai_nama_jadwal_sebagai_judul(): void
    {
        $majelis = $this->makeAssembly();
        $guru = $this->makeTeacher(['foto' => 'guru/uji.webp']);
        $jadwal = $this->makeSchedule($majelis, [
            'nama_jadwal' => 'Kajian Sabilal Muhtadin',
            'teacher_id' => $guru->id,
        ]);

        $html = $this->get(route('jadwal-majelis-detail', $jadwal->route_slug))->assertOk()->getContent();

        $this->assertSame('Kajian Sabilal Muhtadin — Syaikhuna', $this->judul($html));
        $this->assertSame('https://syaikhuna.id/jadwal-majelis/'.$jadwal->route_slug, $this->canonical($html));
    }

    /** @test */
    public function detail_tulisan_memakai_judul_dan_isi_tulisan(): void
    {
        $post = Post::create([
            'user_id' => $this->kontributor()->id,
            'title' => 'Adab Menuntut Ilmu',
            'slug' => 'adab-menuntut-ilmu',
            'content' => '<p>Ilmu itu &amp; cahaya.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $html = $this->get(route('tulisan.detail', $post->slug))->assertOk()->getContent();

        $this->assertSame('Adab Menuntut Ilmu — Syaikhuna', $this->judul($html));
        $this->assertSame('Ilmu itu & cahaya.', $this->meta($html, 'description'));
        $this->assertSame('article', $this->ogType($html));
    }

    /** @test */
    public function meta_keywords_sudah_tidak_dipakai(): void
    {
        $html = $this->get(route('beranda'))->getContent();

        $this->assertNull($this->meta($html, 'keywords'));
    }

    /** @test */
    public function canonical_mengabaikan_query_string(): void
    {
        $html = $this->get(route('guru-list').'?search=zaini&page=2')->assertOk()->getContent();

        $this->assertSame('https://syaikhuna.id/guru', $this->canonical($html));
    }

    /** @test */
    public function og_url_selalu_sama_dengan_canonical(): void
    {
        $html = $this->get(route('majelis-list').'?search=raudhah')->getContent();

        $this->assertSame($this->canonical($html), $this->ogUrl($html));
    }

    /** @test */
    public function profil_kontributor_tidak_diindeks(): void
    {
        $kontributor = $this->kontributor();
        $kontributor->forceFill([
            'username' => 'kontributor-uji',
            'kontributor_since' => now(),
        ])->save();

        $html = $this->get(route('kontributor.profil', 'kontributor-uji'))->assertOk()->getContent();

        $this->assertSame('noindex, follow', $this->meta($html, 'robots'));
    }

    /** @test */
    public function halaman_publik_tidak_memasang_noindex(): void
    {
        foreach (['beranda', 'guru-list', 'majelis-list', 'tulisan.list'] as $routeName) {
            $html = $this->get(route($routeName))->getContent();

            $this->assertNull(
                $this->meta($html, 'robots'),
                "Halaman publik [$routeName] seharusnya tidak memasang meta robots."
            );
        }
    }

    /** @test */
    public function halaman_login_tidak_diindeks(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertSame('noindex, nofollow', $this->meta($html, 'robots'));
    }

    // ---------------------------------------------------------------- helpers

    private function assertMetadataSehat(string $html, string $label): void
    {
        $judul = $this->judul($html);
        $deskripsi = $this->meta($html, 'description');
        $canonical = $this->canonical($html);
        $ogImage = $this->ogImage($html);

        $this->assertNotNull($judul, "[$label] tidak punya <title>.");
        $this->assertNotSame('', trim((string) $judul), "[$label] punya <title> kosong.");
        $this->assertLessThanOrEqual(SeoService::TITLE_LIMIT + 1, mb_strlen($judul), "[$label] judulnya terlalu panjang: $judul");

        $this->assertNotNull($deskripsi, "[$label] tidak punya meta description.");
        $this->assertNotSame('', trim($deskripsi), "[$label] punya meta description kosong.");
        $this->assertLessThanOrEqual(160, mb_strlen($deskripsi), "[$label] deskripsinya terlalu panjang.");
        $this->assertStringNotContainsString('<', $deskripsi, "[$label] deskripsinya masih mengandung tag HTML.");

        $this->assertNotNull($canonical, "[$label] tidak punya <link rel=canonical>.");
        $this->assertStringStartsWith('https://syaikhuna.id/', $canonical, "[$label] canonical tidak absolut.");
        $this->assertStringNotContainsString('?', $canonical, "[$label] canonical masih membawa query string.");

        $this->assertNotNull($ogImage, "[$label] tidak punya og:image.");
        $this->assertStringStartsWith('https://', $ogImage, "[$label] og:image tidak absolut.");
    }

    private function judul(string $html): ?string
    {
        preg_match('/<title>(.*?)<\/title>/s', $html, $m);

        return isset($m[1]) ? $this->decode(trim($m[1])) : null;
    }

    private function meta(string $html, string $name): ?string
    {
        preg_match('/<meta name="'.preg_quote($name, '/').'" content="([^"]*)"/', $html, $m);

        return isset($m[1]) ? $this->decode($m[1]) : null;
    }

    private function property(string $html, string $property): ?string
    {
        preg_match('/<meta property="'.preg_quote($property, '/').'" content="([^"]*)"/', $html, $m);

        return isset($m[1]) ? $this->decode($m[1]) : null;
    }

    private function canonical(string $html): ?string
    {
        preg_match('/<link rel="canonical" href="([^"]*)"/', $html, $m);

        return isset($m[1]) ? $this->decode($m[1]) : null;
    }

    private function ogImage(string $html): ?string
    {
        return $this->property($html, 'og:image');
    }

    private function ogUrl(string $html): ?string
    {
        return $this->property($html, 'og:url');
    }

    private function ogType(string $html): ?string
    {
        return $this->property($html, 'og:type');
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
