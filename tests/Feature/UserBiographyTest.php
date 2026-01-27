<?php

namespace Tests\Feature;

use App\Models\Biography;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBiographyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_biography_list()
    {
        Biography::create([
            'nama' => 'Wali Test',
            'slug' => 'wali-test',
            'deskripsi' => 'Deskripsi',
        ]);

        $response = $this->get(route('manaqib-list'));

        $response->assertStatus(200);
        $response->assertSee('Wali Test');
        $response->assertSeeLivewire('list-biography');
    }

    public function test_user_can_view_biography_detail()
    {
        $bio = Biography::create([
            'nama' => 'Wali Detail',
            'slug' => 'wali-detail',
            'deskripsi' => 'Deskripsi Detail',
            'tanggal_wafat_masehi' => '2023-01-01',
            'maps' => '<iframe>maps</iframe>',
        ]);

        $response = $this->get(route('manaqib-detail', $bio->slug));

        $response->assertStatus(200);
        $response->assertSee('Wali Detail');
        $response->assertSee('Deskripsi Detail');
        $response->assertSee('<iframe>maps</iframe>', false); // false to not escape html
    }
}
