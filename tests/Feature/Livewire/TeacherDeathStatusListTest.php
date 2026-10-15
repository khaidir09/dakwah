<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ListBiography;
use App\Livewire\ListGuru;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherDeathStatusListTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacher(string $name, array $attributes = []): Teacher
    {
        return Teacher::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'biografi' => 'Bio',
            'foto' => 'foto.jpg',
            'domisili' => '-',
        ], $attributes));
    }

    public function test_manaqib_lists_teachers_with_any_recorded_death_date(): void
    {
        $this->makeTeacher('Guru Wafat Masehi', ['wafat_masehi' => '2005-08-10']);
        $this->makeTeacher('Guru Wafat Hijriah', ['wafat_hijriah_year' => 1426]);
        $this->makeTeacher('Guru Masih Hidup');

        Livewire::test(ListBiography::class)
            ->assertSee('Guru Wafat Masehi')
            ->assertSee('Guru Wafat Hijriah')
            ->assertDontSee('Guru Masih Hidup')
            ->assertViewHas('biographies_count', 2);
    }

    public function test_guru_list_excludes_teachers_with_any_recorded_death_date(): void
    {
        $this->makeTeacher('Guru Wafat Masehi', ['wafat_masehi' => '2005-08-10']);
        $this->makeTeacher('Guru Wafat Hijriah', ['wafat_hijriah_year' => 1426]);
        $this->makeTeacher('Guru Masih Hidup');

        Livewire::test(ListGuru::class)
            ->assertSee('Guru Masih Hidup')
            ->assertDontSee('Guru Wafat Masehi')
            ->assertDontSee('Guru Wafat Hijriah')
            ->assertViewHas('teachers_count', 1);
    }
}
