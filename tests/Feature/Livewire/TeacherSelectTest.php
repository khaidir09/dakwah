<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Partials\TeacherSelect;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherSelectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_successfully()
    {
        Livewire::test(TeacherSelect::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_does_not_search_deceased_teachers()
    {
        $teacher = Teacher::create([
            'name' => 'Ustadz Almarhum',
            'biografi' => 'Bio',
            'foto' => 'foto.jpg',
            'domisili' => 'Jakarta',
            'wafat_masehi' => now(),
        ]);

        Livewire::test(TeacherSelect::class)
            ->set('search', 'Almarhum')
            ->assertDontSee('Ustadz Almarhum');
    }

    /** @test */
    public function it_searches_teachers()
    {
        $teacher = Teacher::create([
            'name' => 'Ustadz Fulan',
            'biografi' => 'Bio',
            'foto' => 'foto.jpg',
            'domisili' => 'Jakarta'
        ]);

        Livewire::test(TeacherSelect::class)
            ->set('search', 'Fulan')
            ->assertSee('Ustadz Fulan');
    }

    /** @test */
    public function it_selects_teacher()
    {
        $teacher = Teacher::create([
            'name' => 'Ustadz Fulan',
            'biografi' => 'Bio',
            'foto' => 'foto.jpg',
            'domisili' => 'Jakarta'
        ]);

        Livewire::test(TeacherSelect::class)
            ->call('selectTeacher', $teacher->id, $teacher->name)
            ->assertSet('selectedTeacherId', $teacher->id)
            ->assertSet('selectedTeacherName', $teacher->name)
            ->assertSet('search', $teacher->name);
    }
}
