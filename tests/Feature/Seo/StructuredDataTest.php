<?php

namespace Tests\Feature\Seo;

use Tests\Feature\PublicPageTestCase;

class StructuredDataTest extends PublicPageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://syaikhuna.id']);
    }

    /** @test */
    public function setiap_halaman_publik_memuat_organization_yang_valid(): void
    {
        foreach (['beranda', 'guru-list', 'majelis-list', 'event-list', 'tulisan.list'] as $routeName) {
            $blok = $this->blok($this->get(route($routeName))->getContent());

            $organization = $this->cari($blok, 'Organization');

            $this->assertNotNull($organization, "Halaman [$routeName] tidak memuat Organization.");
            $this->assertSame('https://schema.org', $organization['@context']);
            $this->assertSame('https://syaikhuna.id/', $organization['url']);
            $this->assertNotEmpty($organization['name']);
            $this->assertStringStartsWith('https://', $organization['logo']);
        }
    }

    /**
     * `sameAs` adalah klaim identitas: isinya harus URL profil penuh yang benar-
     * benar dimiliki proyek, bukan nama akun. Yang dijaga di sini bentuknya,
     * bukan sekadar keberadaannya.
     *
     * @test
     */
    public function organization_menyebut_profil_resmi_sebagai_url_penuh(): void
    {
        $organization = $this->cari($this->blok($this->get(route('beranda'))->getContent()), 'Organization');

        $this->assertContains('https://www.instagram.com/syaikhuna.id', $organization['sameAs']);

        foreach ($organization['sameAs'] as $profil) {
            $this->assertStringStartsWith('https://', $profil, "[$profil] bukan URL absolut.");
        }
    }

    /** @test */
    public function beranda_memuat_website_tanpa_klaim_search_action(): void
    {
        $blok = $this->blok($this->get(route('beranda'))->getContent());

        $website = $this->cari($blok, 'WebSite');

        $this->assertNotNull($website);
        $this->assertSame('id-ID', $website['inLanguage']);
        $this->assertArrayNotHasKey('potentialAction', $website);
    }

    /** @test */
    public function halaman_selain_beranda_tidak_memuat_website(): void
    {
        $blok = $this->blok($this->get(route('guru-list'))->getContent());

        $this->assertNull($this->cari($blok, 'WebSite'));
    }

    /** @test */
    public function detail_guru_memuat_person_dengan_data_yang_benar(): void
    {
        $guru = $this->makeTeacher([
            'name' => 'Abah Guru Sekumpul',
            'biografi' => '<p>Ulama besar asal <strong>Martapura</strong>.</p>',
            'foto' => 'guru/large/sekumpul.webp',
        ]);

        $person = $this->cari($this->blok($this->get(route('guru-detail', $guru->slug))->getContent()), 'Person');

        $this->assertNotNull($person);
        $this->assertSame('Abah Guru Sekumpul', $person['name']);
        $this->assertSame('Ulama', $person['jobTitle']);
        $this->assertSame('https://syaikhuna.id/guru/'.$guru->slug, $person['url']);
        $this->assertSame('Ulama besar asal Martapura.', $person['description']);
        $this->assertStringContainsString('guru/large/sekumpul.webp', $person['image']);
    }

    /**
     * E15: properti tanpa data dihilangkan, bukan dikirim bernilai null.
     *
     * @test
     */
    public function guru_tanpa_tanggal_wafat_tidak_mengirim_death_date(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Masih Hidup', 'wafat_masehi' => null]);

        $person = $this->cari($this->blok($this->get(route('guru-detail', $guru->slug))->getContent()), 'Person');

        $this->assertArrayNotHasKey('deathDate', $person);
    }

    /** @test */
    public function guru_dengan_tanggal_wafat_mengirim_death_date_format_iso(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Wafat', 'wafat_masehi' => '2005-08-10']);

        $person = $this->cari($this->blok($this->get(route('guru-detail', $guru->slug))->getContent()), 'Person');

        $this->assertSame('2005-08-10', $person['deathDate']);
    }

    /** @test */
    public function detail_acara_memuat_event_dengan_waktu_dan_lokasi(): void
    {
        $acara = $this->makeEvent([
            'name' => 'Haul Akbar',
            'location' => 'Masjid Agung',
            'moderated_at' => now(),
            'access' => 'Umum',
        ]);

        $event = $this->cari($this->blok($this->get(route('event-detail', $acara->route_slug))->getContent()), 'Event');

        $this->assertNotNull($event);
        $this->assertSame('Haul Akbar', $event['name']);
        $this->assertSame('https://syaikhuna.id/event/'.$acara->route_slug, $event['url']);
        $this->assertSame('https://schema.org/EventScheduled', $event['eventStatus']);
        $this->assertSame('https://schema.org/OfflineEventAttendanceMode', $event['eventAttendanceMode']);
        $this->assertSame('Place', $event['location']['@type']);
        $this->assertSame('Masjid Agung', $event['location']['name']);
        $this->assertSame('Banjarmasin Tengah', $event['location']['address']['addressLocality']);

        // startDate wajib membawa offset zona waktu, kalau tidak Google menafsirkannya
        // sebagai waktu lokal perayap.
        $this->assertMatchesRegularExpression('/[+-]\d{2}:\d{2}$/', $event['startDate']);
    }

    /** @test */
    public function detail_acara_menyebut_majelis_sebagai_penyelenggara(): void
    {
        $majelis = $this->makeAssembly(['nama_majelis' => 'Majelis Ar-Raudhah']);
        $acara = $this->makeEvent([
            'assembly_id' => $majelis->id,
            'moderated_at' => now(),
            'access' => 'Umum',
        ]);

        $event = $this->cari($this->blok($this->get(route('event-detail', $acara->route_slug))->getContent()), 'Event');

        $this->assertSame('Majelis Ar-Raudhah', $event['organizer']['name']);
        $this->assertSame('https://syaikhuna.id/majelis/'.$majelis->route_slug, $event['organizer']['url']);
    }

    /**
     * Nama entitas berasal dari input pengguna. Tanpa JSON_HEX_TAG, tag penutup
     * yang menyelinap di dalamnya akan menutup blok script lebih awal dan
     * membocorkan sisanya sebagai markup.
     *
     * @test
     */
    public function nama_yang_mengandung_tag_script_tidak_memecah_blok_json_ld(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru </script><img src=x> Uji']);

        $html = $this->get(route('guru-detail', $guru->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('</script><img src=x>', $html);

        $person = $this->cari($this->blok($html), 'Person');

        $this->assertNotNull($person, 'Blok Person gagal diurai — kemungkinan blok script terputus.');
        $this->assertSame('Guru </script><img src=x> Uji', $person['name']);
    }

    /** @test */
    public function seluruh_blok_json_ld_dapat_diurai(): void
    {
        $guru = $this->makeTeacher(['name' => 'Guru Urai']);

        foreach ([route('beranda'), route('guru-list'), route('guru-detail', $guru->slug)] as $url) {
            $html = $this->get($url)->getContent();

            preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);

            $this->assertNotEmpty($m[1], "Tidak ada blok JSON-LD di [$url].");

            foreach ($m[1] as $json) {
                $this->assertIsArray(
                    json_decode($json, true),
                    "Blok JSON-LD di [$url] bukan JSON yang valid: ".json_last_error_msg()
                );
            }
        }
    }

    // ---------------------------------------------------------------- helpers

    /**
     * @return array<int, array<string, mixed>>
     */
    private function blok(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);

        return array_map(fn (string $json) => json_decode($json, true) ?? [], $m[1]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $blok
     * @return array<string, mixed>|null
     */
    private function cari(array $blok, string $type): ?array
    {
        foreach ($blok as $data) {
            if (($data['@type'] ?? null) === $type) {
                return $data;
            }
        }

        return null;
    }
}
