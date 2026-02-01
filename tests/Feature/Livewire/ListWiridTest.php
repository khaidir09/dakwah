<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ListWirid;
use App\Models\Wirid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListWiridTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_component_can_render()
    {
        Livewire::test(ListWirid::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_wirid_list()
    {
        Wirid::create([
            'nama' => 'Wirid Test',
            'deskripsi' => 'Deskripsi Test',
            'arab' => 'Arab Test',
            'arti' => 'Arti Test',
            'jumlah' => 1,
            'waktu' => 'Pagi',
            'likes' => 0,
        ]);

        Livewire::test(ListWirid::class)
            ->assertSee('Wirid Test')
            ->assertSee('Deskripsi Test');
    }

    /** @test */
    public function it_displays_whatsapp_share_link()
    {
        $wirid = Wirid::create([
            'nama' => 'Wirid Share Test',
            'deskripsi' => 'Deskripsi Share',
            'arab' => 'Arab Share',
            'arti' => 'Arti Share',
            'jumlah' => 33,
            'waktu' => 'Malam',
            'likes' => 10,
        ]);

        // Expectation:
        // Link: https://wa.me/?text=...
        // Message contains: route('wirid-list', ['search' => 'Wirid Share Test'])
        // URL Encoded: %3Fsearch%3DWirid%2BShare%2BTest (or similar)

        Livewire::test(ListWirid::class)
            ->assertSee('Wirid Share Test')
            ->assertSeeHtml('https://wa.me/?text=')
            ->assertSeeHtml(urlencode($wirid->nama))
            ->assertSeeHtml('search%3D'); // search= encoded
    }
}
