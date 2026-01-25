<?php

namespace Tests\Feature;

use App\Livewire\ListGuru;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Laravolt\Indonesia\Models\Province;

class ListGuruTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_guru_renders_successfully()
    {
        // Mocking Province because ListGuru query uses it in render to populate dropdowns
        if (Province::count() === 0) {
            Province::create(['code' => '62', 'name' => 'Kalimantan Tengah']);
        }

        Teacher::create([
            'name' => 'Guru Test',
            'domisili' => 'Alamat Test',
            'biografi' => 'Bio Test',
            'foto' => 'default.jpg'
        ]);

        Livewire::test(ListGuru::class)
            ->assertStatus(200)
            ->assertSee('Guru Test');
    }

    public function test_list_guru_filters_by_province()
    {
        // Setup data
        Province::create(['code' => '62', 'name' => 'Kalimantan Tengah']);
        Province::create(['code' => '33', 'name' => 'Jawa Tengah']);

        Teacher::create([
            'name' => 'Guru Kalimantan',
            'province_code' => '62',
            'domisili' => 'Alamat Kalteng',
            'biografi' => 'Bio Kalteng',
            'foto' => 'kalteng.jpg'
        ]);

        Teacher::create([
            'name' => 'Guru Jawa',
            'province_code' => '33',
            'domisili' => 'Alamat Jateng',
            'biografi' => 'Bio Jateng',
            'foto' => 'jateng.jpg'
        ]);

        // Test Filter
        Livewire::test(ListGuru::class)
            ->set('selectedProvince', '62')
            ->assertSee('Guru Kalimantan')
            ->assertDontSee('Guru Jawa');
    }
}
