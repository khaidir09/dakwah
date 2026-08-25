<?php

namespace Tests\Feature\Seo;

use App\Http\Middleware\AddNoindexHeader;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\PublicPageTestCase;

class NoindexTest extends PublicPageTestCase
{
    private const HEADER = 'X-Robots-Tag';

    /** @test */
    public function halaman_pengaturan_akun_mengirim_header_noindex(): void
    {
        $this->actingAs($this->kontributor())
            ->get(route('pengaturan-akun'))
            ->assertOk()
            ->assertHeader(self::HEADER, AddNoindexHeader::DIRECTIVES);
    }

    /** @test */
    public function area_swalayan_pengguna_mengirim_header_noindex(): void
    {
        $this->actingAs($this->kontributor())
            ->get(route('favorit-saya'))
            ->assertOk()
            ->assertHeader(self::HEADER, AddNoindexHeader::DIRECTIVES);
    }

    /** @test */
    public function area_admin_mengirim_header_noindex(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/schedule-notes')
            ->assertOk()
            ->assertHeader(self::HEADER, AddNoindexHeader::DIRECTIVES);
    }

    /**
     * Inilah alasan memakai header dan bukan hanya <meta name="robots">:
     * response ini adalah berkas, tidak punya tempat untuk meta tag.
     *
     * @test
     */
    public function unduhan_bergerbang_membawa_header_meski_bukan_html(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('lampiran/kajian.pdf', '%PDF-1.4 uji');

        $post = Post::create([
            'user_id' => $this->kontributor()->id,
            'title' => 'Tulisan Berlampiran',
            'slug' => 'tulisan-berlampiran',
            'content' => '<p>Isi tulisan.</p>',
            'status' => 'published',
            'published_at' => now(),
            'attachment_path' => 'lampiran/kajian.pdf',
            'attachment_filename' => 'kajian.pdf',
        ]);

        $response = $this->actingAs($this->kontributor())
            ->get(route('tulisan.download', $post->slug));

        $response->assertOk()->assertHeader(self::HEADER, AddNoindexHeader::DIRECTIVES);
        $this->assertStringNotContainsString('text/html', (string) $response->headers->get('Content-Type'));
    }

    /**
     * `auth` terdaftar di $middlewarePriority Laravel sehingga selalu berjalan
     * lebih dulu — pengalihan ke login tidak membawa header ini. Itu tidak apa:
     * perayap anonim tidak pernah menerima isi apa pun, dan halaman login sudah
     * `noindex` lewat layout-nya. Test ini mengunci alasannya agar celah semu
     * ini tidak terus-menerus "diperbaiki".
     *
     * @test
     */
    public function pengalihan_ke_login_tidak_membawa_isi_yang_perlu_ditandai(): void
    {
        $this->get(route('pengaturan-akun'))
            ->assertRedirect(route('login'))
            ->assertHeaderMissing(self::HEADER);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    /** @test */
    public function halaman_publik_tidak_mengirim_header_noindex(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Publik', 'foto' => 'guru/large/a.webp']);

        $halaman = [
            route('beranda'),
            route('guru-list'),
            route('guru-detail', $guru->slug),
            route('event-list'),
            route('manaqib-list'),
            route('sitemap'),
        ];

        foreach ($halaman as $url) {
            $this->get($url)->assertOk()->assertHeaderMissing(self::HEADER);
        }
    }

    /**
     * Profil kontributor terbuka untuk umum, jadi tidak boleh kena header ini;
     * pembatasannya cukup lewat meta `noindex, follow` di view.
     *
     * @test
     */
    public function daftar_kontributor_publik_tidak_kena_header(): void
    {
        $this->get(route('kontributor.index'))
            ->assertOk()
            ->assertHeaderMissing(self::HEADER);
    }
}
