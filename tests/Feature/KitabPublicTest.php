<?php

namespace Tests\Feature;

use App\Http\Controllers\SitemapController;
use App\Livewire\HomeKitabUnggulan;
use App\Livewire\ListKitab;
use App\Models\Book;
use App\Models\Library;
use App\Models\Specialization;
use App\Models\Teacher;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

class KitabPublicTest extends PublicPageTestCase
{
    private function makeBook(array $teachers, array $attributes = []): Book
    {
        $title = $attributes['title'] ?? 'Kitab Uji';
        $book = Book::create(array_merge([
            'title' => $title,
            'slug' => Book::generateSlug($title),
            'is_active' => true,
        ], $attributes));

        foreach ($teachers as $index => [$teacher, $role]) {
            $book->teachers()->attach($teacher->id, ['role' => $role, 'sort_order' => $index]);
        }

        return $book;
    }

    public function test_daftar_hanya_menampilkan_kitab_aktif(): void
    {
        $teacher = $this->makeTeacher();
        $this->makeBook([[$teacher, 'pengarang']], ['title' => 'Kitab Tayang']);
        $this->makeBook([[$teacher, 'pengarang']], ['title' => 'Kitab Draf', 'is_active' => false]);

        $this->get(route('kitab-list'))
            ->assertOk()
            ->assertSee('Kitab Tayang')
            ->assertDontSee('Kitab Draf');
    }

    public function test_detail_draf_hanya_untuk_super_admin(): void
    {
        $teacher = $this->makeTeacher();
        $book = $this->makeBook([[$teacher, 'pengarang']], ['is_active' => false]);

        $this->get(route('kitab-detail', $book))->assertNotFound();
        $this->actingAs($this->kontributor())->get(route('kitab-detail', $book))->assertNotFound();
        $this->actingAs($this->superAdmin())->get(route('kitab-detail', $book))
            ->assertOk()
            ->assertSee('noindex, nofollow', false);
    }

    public function test_detail_menampilkan_guru_publik_dengan_label_wafat(): void
    {
        $masehi = $this->makeTeacher(['name' => 'Syekh Arsyad', 'wafat_masehi' => '1812-10-03']);
        $hijriah = $this->makeTeacher(['name' => 'Guru Hijriah', 'wafat_hijriah_year' => 1227]);
        $hidup = $this->makeTeacher(['name' => 'Guru Hidup']);
        $pending = $this->makeTeacher(['name' => 'Guru Pending', 'contribution_status' => 'pending']);

        $book = $this->makeBook([
            [$masehi, 'pengarang'],
            [$hijriah, 'pensyarah'],
            [$hidup, 'penerjemah'],
            [$pending, 'pentahqiq'],
        ], ['title_arabic' => 'سبيل المهتدين']);

        $response = $this->get(route('kitab-detail', $book))->assertOk();

        $response->assertSee('سبيل المهتدين')
            ->assertSee(route('guru-detail', $masehi))
            ->assertSee('wafat 1812 M')
            ->assertSee('wafat 1227 H')
            ->assertSee('Guru Hidup')
            ->assertDontSee('Guru Pending')
            ->assertDontSee('Pentahqiq');

        $this->assertSame(2, substr_count($response->getContent(), 'rahimahullah'));
    }

    public function test_tombol_pustaka_hanya_untuk_pustaka_aktif(): void
    {
        $teacher = $this->makeTeacher();
        $library = Library::create([
            'title' => 'Naskah Digital',
            'slug' => 'naskah-digital',
            'category' => 'Fikih',
            'description' => 'Desc',
            'file_path' => 'libraries/files/x.pdf',
            'price_type' => 'free',
            'is_active' => true,
        ]);
        $book = $this->makeBook([[$teacher, 'pengarang']], ['library_id' => $library->id]);

        $this->get(route('kitab-detail', $book))
            ->assertSee('Baca di Pustaka')
            ->assertSee(route('pustaka-detail', $library));

        $library->update(['is_active' => false]);
        $this->get(route('kitab-detail', $book))->assertDontSee('Baca di Pustaka');

        $library->delete();
        $this->assertNull($book->fresh()->library_id);
        $this->get(route('kitab-detail', $book))->assertOk()->assertDontSee('Baca di Pustaka');
    }

    public function test_filter_status_guru_bidang_dan_pencarian(): void
    {
        $fiqih = Specialization::forceCreate(['name' => 'FIQIH']);
        $wafat = $this->makeTeacher(['name' => 'Syekh Arsyad', 'wafat_hijriah_year' => 1227]);
        $hidup = $this->makeTeacher(['name' => 'Guru Muda']);

        $this->makeBook([[$wafat, 'pengarang']], ['title' => 'Sabilal Muhtadin', 'title_arabic' => 'سبيل المهتدين', 'specialization_id' => $fiqih->id]);
        $this->makeBook([[$hidup, 'pengarang']], ['title' => 'Risalah Baru', 'language' => 'indonesia']);

        Livewire::test(ListKitab::class)
            ->set('teacher_status', 'wafat')
            ->assertSee('Sabilal Muhtadin')
            ->assertDontSee('Risalah Baru')
            ->set('teacher_status', 'hidup')
            ->assertSee('Risalah Baru')
            ->assertDontSee('Sabilal Muhtadin');

        Livewire::test(ListKitab::class)
            ->set('specialization', $fiqih->id)
            ->assertSee('Sabilal Muhtadin')
            ->assertDontSee('Risalah Baru');

        Livewire::test(ListKitab::class)
            ->set('language', 'indonesia')
            ->assertSee('Risalah Baru')
            ->assertDontSee('Sabilal Muhtadin');

        Livewire::test(ListKitab::class)
            ->set('search', 'Arsyad')
            ->assertSee('Sabilal Muhtadin')
            ->assertDontSee('Risalah Baru');

        Livewire::test(ListKitab::class)
            ->set('search', 'المهتدين')
            ->assertSee('Sabilal Muhtadin')
            ->assertDontSee('Risalah Baru');
    }

    public function test_pencarian_tidak_menemukan_lewat_nama_guru_non_publik(): void
    {
        $pending = $this->makeTeacher(['name' => 'Rahasia Pending', 'contribution_status' => 'pending']);
        $this->makeBook([[$pending, 'pengarang']], ['title' => 'Kitab Sunyi']);

        Livewire::test(ListKitab::class)
            ->set('search', 'Rahasia')
            ->assertDontSee('Kitab Sunyi');
    }

    public function test_profil_guru_menampilkan_karya_dengan_peran(): void
    {
        $teacher = $this->makeTeacher(['name' => 'Syekh Arsyad']);
        $tanpaKitab = $this->makeTeacher(['name' => 'Guru Tanpa Kitab']);
        $this->makeBook([[$teacher, 'pengarang'], [$teacher, 'pensyarah']], ['title' => 'Kitab Dua Peran']);
        $this->makeBook([[$teacher, 'pengarang']], ['title' => 'Kitab Draf', 'is_active' => false]);

        $response = $this->get(route('guru-detail', $teacher))->assertOk();
        $response->assertSee('Karya')
            ->assertSee('Pengarang, Pensyarah')
            ->assertDontSee('Kitab Draf');
        $this->assertSame(1, substr_count($response->getContent(), 'Kitab Dua Peran</h3>'));

        $this->get(route('guru-detail', $tanpaKitab))->assertOk()->assertDontSee('Karya</h2>', false);
    }

    public function test_manaqib_menampilkan_karya_dengan_peran(): void
    {
        $teacher = $this->makeTeacher(['name' => 'Syekh Arsyad']);
        $tanpaKitab = $this->makeTeacher(['name' => 'Guru Tanpa Kitab']);
        $this->makeBook([[$teacher, 'pengarang'], [$teacher, 'pensyarah']], ['title' => 'Kitab Dua Peran']);
        $this->makeBook([[$teacher, 'pengarang']], ['title' => 'Kitab Draf', 'is_active' => false]);

        $response = $this->get(route('manaqib-detail', $teacher->slug))->assertOk();
        $response->assertSee('Karya</h3>', false)
            ->assertSee('Pengarang, Pensyarah')
            ->assertDontSee('Kitab Draf');
        $this->assertSame(1, substr_count($response->getContent(), 'Kitab Dua Peran</h3>'));

        $this->get(route('manaqib-detail', $tanpaKitab->slug))->assertOk()->assertDontSee('Karya</h3>', false);
    }

    public function test_widget_beranda_hanya_kitab_aktif_unggulan_maksimal_enam(): void
    {
        Cache::forget(HomeKitabUnggulan::CACHE_KEY);
        Livewire::test(HomeKitabUnggulan::class)->assertDontSee('Kitab Ulama');

        $teacher = $this->makeTeacher();
        foreach (range(1, 7) as $i) {
            $this->makeBook([[$teacher, 'pengarang']], ['title' => "Unggulan {$i}", 'is_featured' => true]);
        }
        $this->makeBook([[$teacher, 'pengarang']], ['title' => 'Biasa Saja']);
        $this->makeBook([[$teacher, 'pengarang']], ['title' => 'Draf Unggul', 'is_featured' => true, 'is_active' => false]);

        Cache::forget(HomeKitabUnggulan::CACHE_KEY);
        $html = Livewire::test(HomeKitabUnggulan::class)
            ->assertSee('Kitab Ulama')
            ->assertDontSee('Biasa Saja')
            ->assertDontSee('Draf Unggul')
            ->html();

        $this->assertSame(6, substr_count($html, 'Unggulan '));
    }

    public function test_beranda_tetap_dapat_dirender(): void
    {
        $this->get(route('beranda'))->assertOk();
    }

    public function test_sitemap_hanya_memuat_kitab_aktif(): void
    {
        Cache::forget(SitemapController::CACHE_KEY);
        $teacher = $this->makeTeacher();
        $aktif = $this->makeBook([[$teacher, 'pengarang']], ['title' => 'Kitab Tayang']);
        $draf = $this->makeBook([[$teacher, 'pengarang']], ['title' => 'Kitab Draf', 'is_active' => false]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('/kitab/'.$aktif->slug, false)
            ->assertDontSee('/kitab/'.$draf->slug, false)
            ->assertSee('/kitab<', false);
    }

    public function test_status_wafat_konsisten_antara_model_dan_scope(): void
    {
        $kasus = [
            'tanpa data' => [[], false],
            'masehi saja' => [['wafat_masehi' => '1990-01-01'], true],
            'hijriah saja' => [['wafat_hijriah_year' => 1410], true],
            'keduanya' => [['wafat_masehi' => '1990-01-01', 'wafat_hijriah_year' => 1410], true],
        ];

        foreach ($kasus as $nama => [$attributes, $expected]) {
            $teacher = $this->makeTeacher(array_merge(['name' => 'Guru '.$nama], $attributes));

            $this->assertSame($expected, $teacher->isWafat(), $nama);
            $this->assertSame($expected, Teacher::wafat()->whereKey($teacher->id)->exists(), $nama);
            $this->assertSame(! $expected, Teacher::masihHidup()->whereKey($teacher->id)->exists(), $nama);
        }
    }
}
