<?php

namespace Tests\Feature;

use App\Models\Wirid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class WiridMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_wirids_table_has_kategori_column()
    {
        $this->assertTrue(Schema::hasColumn('wirids', 'kategori'));
    }

    public function test_wirid_model_scopes()
    {
        Wirid::create(['nama' => 'Test Wirid', 'arab' => '...','jumlah'=>1, 'kategori' => 'wirid', 'deskripsi' => '-']);
        Wirid::create(['nama' => 'Test Doa', 'arab' => '...','jumlah'=>1, 'kategori' => 'doa', 'deskripsi' => '-']);

        $this->assertEquals(1, Wirid::wirid()->count());
        $this->assertEquals(1, Wirid::doa()->count());
        $this->assertEquals('Test Wirid', Wirid::wirid()->first()->nama);
        $this->assertEquals('Test Doa', Wirid::doa()->first()->nama);
    }

    public function test_kategori_default_value()
    {
         $wirid = Wirid::create(['nama' => 'Test Default', 'arab' => '...', 'jumlah' => 1, 'deskripsi' => '-']);
         // Database default is 'wirid', but if I don't pass it in create(),
         // Eloquent doesn't fetch default from DB unless I refresh or let DB handle it.
         // If I don't pass it, and it's not nullable, it should take default.
         $wirid->refresh();
         $this->assertEquals('wirid', $wirid->kategori);
    }
}
