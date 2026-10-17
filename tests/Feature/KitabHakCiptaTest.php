<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Teacher;

class KitabHakCiptaTest extends PublicPageTestCase
{
    /**
     * @param  array<int, array{0: Teacher, 1: string}>  $teachers  pasangan [guru, peran]
     */
    private function makeBook(array $teachers, string $title = 'Kitab Uji'): Book
    {
        $book = Book::create([
            'title' => $title,
            'slug' => Book::generateSlug($title),
            'is_active' => true,
        ]);

        foreach ($teachers as $index => [$teacher, $role]) {
            $book->teachers()->attach($teacher->id, ['role' => $role, 'sort_order' => $index]);
        }

        return $book->load('teachers');
    }

    public function test_tahun_wafat_masehi_dari_tanggal_masehi(): void
    {
        $teacher = $this->makeTeacher(['wafat_masehi' => '1812-10-03', 'wafat_hijriah_year' => 1227]);

        $this->assertSame(1812, $teacher->tahunWafatMasehi());
    }

    public function test_tahun_wafat_hanya_hijriah_memakai_tahun_masehi_paling_akhir(): void
    {
        // 1426 H berlangsung Feb 2005 – Feb 2006.
        $this->assertSame(2006, $this->makeTeacher(['name' => 'Guru A', 'wafat_hijriah_year' => 1426])->tahunWafatMasehi());
        // 1227 H berlangsung Jan 1812 – Jan 1813.
        $this->assertSame(1813, $this->makeTeacher(['name' => 'Guru B', 'wafat_hijriah_year' => 1227])->tahunWafatMasehi());
        $this->assertNull($this->makeTeacher(['name' => 'Guru C'])->tahunWafatMasehi());
    }

    public function test_pengarang_wafat_lebih_dari_70_tahun_domain_publik(): void
    {
        $book = $this->makeBook([[$this->makeTeacher(['wafat_masehi' => '1812-10-03']), 'pengarang']]);

        $this->assertSame(1883, $book->publicDomainYear());
        $this->assertSame('domain_publik', $book->copyrightStatus());
        $this->assertSame('Domain Publik', $book->copyright_status_label);
    }

    public function test_batas_masa_perlindungan_dihitung_sejak_1_januari_tahun_berikutnya(): void
    {
        $book = $this->makeBook([[$this->makeTeacher(['wafat_masehi' => '2005-08-10']), 'pengarang']]);

        $this->assertSame(2076, $book->publicDomainYear());

        $this->travelTo('2075-12-31 23:59:59');
        $this->assertSame('dilindungi', $book->copyrightStatus());

        $this->travelTo('2076-01-01 00:00:00');
        $this->assertSame('domain_publik', $book->copyrightStatus());
    }

    public function test_karya_bersama_mengikuti_pencipta_terakhir_wafat(): void
    {
        $book = $this->makeBook([
            [$this->makeTeacher(['name' => 'Pengarang', 'wafat_masehi' => '1812-10-03']), 'pengarang'],
            [$this->makeTeacher(['name' => 'Pensyarah', 'wafat_masehi' => '2005-08-10']), 'pensyarah'],
        ]);

        $this->assertSame(2076, $book->publicDomainYear());
        $this->assertSame('dilindungi', $book->copyrightStatus());
    }

    public function test_pencipta_belum_tercatat_wafat_dilindungi(): void
    {
        $book = $this->makeBook([
            [$this->makeTeacher(['name' => 'Pengarang', 'wafat_masehi' => '1812-10-03']), 'pengarang'],
            [$this->makeTeacher(['name' => 'Pensyarah Hidup']), 'pensyarah'],
        ]);

        $this->assertNull($book->publicDomainYear());
        $this->assertSame('dilindungi', $book->copyrightStatus());
    }

    public function test_penerjemah_dan_pentahqiq_tidak_dihitung_sebagai_pencipta(): void
    {
        $book = $this->makeBook([
            [$this->makeTeacher(['name' => 'Pengarang', 'wafat_masehi' => '1812-10-03']), 'pengarang'],
            [$this->makeTeacher(['name' => 'Pentahqiq Hidup']), 'pentahqiq'],
            [$this->makeTeacher(['name' => 'Penerjemah Hidup']), 'penerjemah'],
        ]);

        $this->assertSame('domain_publik', $book->copyrightStatus());
    }

    public function test_kitab_tanpa_pengarang_atau_pensyarah_belum_diketahui(): void
    {
        $this->assertSame('belum_diketahui', $this->makeBook([])->copyrightStatus());

        $book = $this->makeBook([[$this->makeTeacher(['name' => 'Penerjemah']), 'penerjemah']], 'Kitab Terjemah');

        $this->assertNull($book->publicDomainYear());
        $this->assertSame('belum_diketahui', $book->copyrightStatus());
    }

    public function test_daftar_kitab_admin_menampilkan_status_hak_cipta(): void
    {
        $this->makeBook([[$this->makeTeacher(['name' => 'Syekh Arsyad', 'wafat_masehi' => '1812-10-03']), 'pengarang']], 'Sabilal Muhtadin');
        $this->makeBook([[$this->makeTeacher(['name' => 'Guru Sekumpul', 'wafat_masehi' => '2005-08-10']), 'pengarang']], 'Risalah Mubarakah');

        $this->actingAs($this->superAdmin())
            ->get(route('admin.kitab.index'))
            ->assertOk()
            ->assertSee('Hak Cipta')
            ->assertSee('Domain Publik')
            ->assertSee('sejak 1 Jan 1883')
            ->assertSee('Dilindungi')
            ->assertSee('hingga 31 Des 2075');
    }
}
