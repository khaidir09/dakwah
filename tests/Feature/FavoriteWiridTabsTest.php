<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wirid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\User\FavoriteWiridList;

class FavoriteWiridTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_favorites_with_tabs()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $wirid = Wirid::create(['nama' => 'Wirid Fav', 'kategori' => 'wirid', 'arab' => '...', 'jumlah' => 1, 'deskripsi' => '-']);
        $doa = Wirid::create(['nama' => 'Doa Fav', 'kategori' => 'doa', 'arab' => '...', 'jumlah' => 1, 'deskripsi' => '-']);

        // User likes both
        $user->likedWirids()->attach($wirid);
        $user->likedWirids()->attach($doa);

        Livewire::test(FavoriteWiridList::class)
            ->assertSee('Wirid') // Tab name
            ->assertSee('Doa') // Tab name
            ->set('kategori', 'wirid')
            ->assertSee('Wirid Fav')
            ->assertDontSee('Doa Fav')
            ->set('kategori', 'doa')
            ->assertSee('Doa Fav')
            ->assertDontSee('Wirid Fav');
    }

    public function test_empty_state_message_updates()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(FavoriteWiridList::class)
            ->set('kategori', 'wirid')
            ->assertSee('Belum ada wirid favorit')
            ->set('kategori', 'doa')
            ->assertSee('Belum ada doa favorit');
    }
}
