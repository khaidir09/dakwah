<?php

namespace Tests\Feature\Seo;

use App\Http\Controllers\SitemapController;
use App\Models\Foundation;
use App\Models\Library;
use App\Models\Post;
use App\Models\ScheduleNote;
use App\Models\ScientificArticle;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\PublicPageTestCase;

class SitemapTest extends PublicPageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://syaikhuna.id']);
    }

    /** @test */
    public function sitemap_dapat_diakses_sebagai_xml_valid(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->getContent());
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $this->assertNotFalse($xml, 'Sitemap bukan XML yang valid.');
        $this->assertSame([], $errors, 'Sitemap memicu error parser XML.');
        $this->assertSame('urlset', $xml->getName());
    }

    /** @test */
    public function sitemap_memuat_halaman_daftar_publik(): void
    {
        $locs = $this->locs();

        foreach (['/', '/guru', '/majelis', '/jadwal-majelis', '/tulisan', '/pustaka', '/wirid', '/video', '/manaqib', '/catatan-pengajian', '/tentang-kami', '/kontributor'] as $path) {
            $this->assertContains(
                'https://syaikhuna.id'.($path === '/' ? '/' : $path),
                $locs,
                "Halaman daftar [$path] tidak ada di sitemap."
            );
        }
    }

    /** @test */
    public function sitemap_memuat_konten_yang_sah_tayang(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Tayang', 'foto' => 'guru/uji.webp']);
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Tayang']);
        $jadwal = $this->makeSchedule($majelis, ['nama_jadwal' => 'Kajian Tayang', 'teacher_id' => $guru->id]);
        $tulisan = $this->tulisan('published');
        $pustaka = $this->pustaka(['price_type' => 'free']);

        $locs = $this->locs();

        $this->assertContains('https://syaikhuna.id/guru/'.$guru->slug, $locs);
        $this->assertContains('https://syaikhuna.id/majelis/'.$majelis->route_slug, $locs);
        $this->assertContains('https://syaikhuna.id/jadwal-majelis/'.$jadwal->route_slug, $locs);
        $this->assertContains('https://syaikhuna.id/tulisan/'.$tulisan->slug, $locs);
        $this->assertContains('https://syaikhuna.id/pustaka/'.$pustaka->slug, $locs);
    }

    /** @test */
    public function sitemap_tidak_memuat_konten_yang_belum_disetujui(): void
    {
        $pemilik = $this->kontributor();

        $guruPending = $this->makeTeacher([
            'name' => 'Guru Pending',
            'contribution_status' => 'pending',
            'contributor_user_id' => $pemilik->id,
        ]);
        $guruDitolak = $this->makeTeacher([
            'name' => 'Guru Ditolak',
            'contribution_status' => 'rejected',
            'contributor_user_id' => $pemilik->id,
        ]);
        $majelisPending = $this->makeAssembly([
            'nama_majelis' => 'Majelis Pending',
            'contribution_status' => 'pending',
            'user_id' => $pemilik->id,
        ]);
        $jadwalPending = $this->makeSchedule($majelisPending, [
            'nama_jadwal' => 'Kajian Pending',
            'contribution_status' => 'pending',
            'contributor_user_id' => $pemilik->id,
        ]);

        $locs = $this->locs();

        $this->assertNotContains('https://syaikhuna.id/guru/'.$guruPending->slug, $locs);
        $this->assertNotContains('https://syaikhuna.id/guru/'.$guruDitolak->slug, $locs);
        $this->assertNotContains('https://syaikhuna.id/majelis/'.$majelisPending->route_slug, $locs);
        $this->assertNotContains('https://syaikhuna.id/jadwal-majelis/'.$jadwalPending->route_slug, $locs);
    }

    /** @test */
    public function sitemap_tidak_memuat_tulisan_draft_maupun_artikel_belum_terbit(): void
    {
        $draft = $this->tulisan('draft');
        $artikelDraft = $this->artikel('DRAFT');

        $locs = $this->locs();

        $this->assertNotContains('https://syaikhuna.id/tulisan/'.$draft->slug, $locs);
        $this->assertNotContains('https://syaikhuna.id/artikel/'.$artikelDraft->slug, $locs);
    }

    /** @test */
    public function sitemap_tidak_memuat_pustaka_berbayar_maupun_nonaktif(): void
    {
        $berbayar = $this->pustaka(['title' => 'Kitab Berbayar', 'slug' => 'kitab-berbayar', 'price_type' => 'paid', 'price' => 50000]);
        $nonaktif = $this->pustaka(['title' => 'Kitab Nonaktif', 'slug' => 'kitab-nonaktif', 'is_active' => false]);

        $locs = $this->locs();

        $this->assertNotContains('https://syaikhuna.id/pustaka/'.$berbayar->slug, $locs);
        $this->assertNotContains('https://syaikhuna.id/pustaka/'.$nonaktif->slug, $locs);
    }

    /**
     * Pustaka gratis tanpa `price_type` eksplisit ikut terdaftar — kolomnya
     * NOT NULL DEFAULT 'free', jadi baris seperti ini memang jatuh ke 'free'.
     *
     * @test
     */
    public function pustaka_tanpa_price_type_eksplisit_tetap_masuk_sitemap(): void
    {
        $pustaka = $this->pustaka(['title' => 'Kitab Lama', 'slug' => 'kitab-lama']);

        $this->assertContains('https://syaikhuna.id/pustaka/'.$pustaka->slug, $this->locs());
    }

    /** @test */
    public function sitemap_hanya_memuat_catatan_pengajian_publik_yang_disetujui(): void
    {
        $tayang = $this->catatan(['visibility' => 'Public', 'status' => 'Approved']);
        $privat = $this->catatan(['visibility' => 'Private', 'status' => 'Approved']);
        $menunggu = $this->catatan(['visibility' => 'Public', 'status' => 'Pending']);

        $locs = $this->locs();

        $this->assertContains('https://syaikhuna.id/catatan-pengajian/'.$tayang->id, $locs);
        $this->assertNotContains('https://syaikhuna.id/catatan-pengajian/'.$privat->id, $locs);
        $this->assertNotContains('https://syaikhuna.id/catatan-pengajian/'.$menunggu->id, $locs);
    }

    /** @test */
    public function sitemap_tidak_memuat_acara_ramadhan_maupun_detail_manaqib(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Manaqib']);
        $majelis = $this->makeAssembly();
        $this->makeEvent(['assembly_id' => $majelis->id, 'moderated_at' => now()]);

        $locs = $this->locs();

        foreach ($locs as $loc) {
            $this->assertStringNotContainsString('/event', $loc);
            $this->assertStringNotContainsString('/jadwal-ramadhan', $loc);
        }

        $this->assertNotContains('https://syaikhuna.id/manaqib/'.$guru->slug, $locs);
    }

    /** @test */
    public function seluruh_url_absolut_tanpa_query_string(): void
    {
        $this->isiKontenLengkap();

        foreach ($this->locs() as $loc) {
            $this->assertStringStartsWith('https://syaikhuna.id/', $loc);
            $this->assertStringNotContainsString('?', $loc);
        }
    }

    /**
     * Menjamin sitemap dan canonical konsisten: URL yang 301 atau 404 di sitemap
     * membuang crawl budget dan mengirim sinyal yang saling bertentangan.
     *
     * @test
     */
    public function setiap_url_di_sitemap_mengembalikan_200(): void
    {
        $this->isiKontenLengkap();

        foreach ($this->locs() as $loc) {
            $path = parse_url($loc, PHP_URL_PATH);

            $this->get($path)->assertStatus(200, "URL sitemap [$loc] tidak mengembalikan 200.");
        }
    }

    /** @test */
    public function entri_konten_memakai_lastmod_format_w3c(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Lastmod']);

        $entri = $this->entriUntuk('https://syaikhuna.id/guru/'.$guru->slug);

        $this->assertNotNull($entri, 'Entri guru tidak ditemukan di sitemap.');
        $this->assertSame($guru->updated_at->toAtomString(), (string) $entri->lastmod);
    }

    /** @test */
    public function sitemap_disimpan_di_cache(): void
    {
        $this->get('/sitemap.xml')->assertOk();

        $this->assertTrue(Cache::has(SitemapController::CACHE_KEY));

        // Konten baru belum tampak sampai cache kedaluwarsa — konsekuensi yang
        // diterima demi menghindari query berat pada tiap perayapan.
        $guru = $this->makeTeacher(['name' => 'Guru Setelah Cache']);
        $this->assertNotContains('https://syaikhuna.id/guru/'.$guru->slug, $this->locs());

        Cache::forget(SitemapController::CACHE_KEY);
        $this->assertContains('https://syaikhuna.id/guru/'.$guru->slug, $this->locs());
    }

    // ---------------------------------------------------------------- helpers

    /**
     * @return array<int, string>
     */
    private function locs(): array
    {
        $xml = simplexml_load_string($this->get('/sitemap.xml')->getContent());

        $locs = [];

        foreach ($xml->url as $url) {
            $locs[] = (string) $url->loc;
        }

        return $locs;
    }

    private function entriUntuk(string $loc): ?\SimpleXMLElement
    {
        $xml = simplexml_load_string($this->get('/sitemap.xml')->getContent());

        foreach ($xml->url as $url) {
            if ((string) $url->loc === $loc) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Satu baris tayang untuk tiap jenis konten, dipakai test yang menelusuri
     * seluruh isi sitemap.
     */
    private function isiKontenLengkap(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Lengkap', 'foto' => 'guru/uji.webp']);
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Lengkap']);
        $this->makeSchedule($majelis, ['nama_jadwal' => 'Kajian Lengkap', 'teacher_id' => $guru->id]);
        $this->tulisan('published');
        $this->artikel('PUBLISHED');
        $this->pustaka(['price_type' => 'free']);
        $this->catatan(['visibility' => 'Public', 'status' => 'Approved']);
    }

    private function tulisan(string $status): Post
    {
        return Post::create([
            'user_id' => $this->kontributor()->id,
            'title' => 'Tulisan '.$status,
            'slug' => 'tulisan-'.$status,
            'content' => '<p>Isi tulisan.</p>',
            'status' => $status,
            'published_at' => now(),
        ]);
    }

    private function artikel(string $status): ScientificArticle
    {
        $foundation = Foundation::create([
            'name' => 'Yayasan Uji '.$status,
            'logo_path' => 'foundations/logo.png',
            'website_url' => 'https://example.test',
        ]);

        return ScientificArticle::create([
            'foundation_id' => $foundation->id,
            'title' => 'Artikel '.$status,
            'subtitle' => 'Subjudul',
            'slug' => 'artikel-'.strtolower($status),
            'author_name' => 'Penulis Uji',
            'category' => 'Sains & Syariat',
            'published_at' => now(),
            'status' => $status,
        ]);
    }

    private function pustaka(array $attributes = []): Library
    {
        return Library::create(array_merge([
            'title' => 'Kitab Uji',
            'slug' => 'kitab-uji',
            'category' => 'Fikih',
            'description' => 'Deskripsi kitab.',
            'file_path' => 'pustaka/kitab-uji.pdf',
            'is_active' => true,
        ], $attributes));
    }

    private function catatan(array $attributes = []): ScheduleNote
    {
        $majelis = $this->makeAssembly();
        $guru = $this->makeTeacher(['name' => 'Guru Catatan '.uniqid()]);
        $jadwal = $this->makeSchedule($majelis, ['teacher_id' => $guru->id]);

        return ScheduleNote::create(array_merge([
            'user_id' => $this->kontributor()->id,
            'schedule_id' => $jadwal->id,
            'content' => 'Isi catatan pengajian.',
            'visibility' => 'Public',
            'status' => 'Approved',
        ], $attributes));
    }
}
