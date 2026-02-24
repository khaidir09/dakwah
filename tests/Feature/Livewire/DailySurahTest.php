<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DailySurah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DailySurahTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:2fl+Ktvkfl+Fuz4Qh/Lx1uwMfwSbcKoAprn67x79FOY=']);
    }

    /** @test */
    public function it_displays_author_information()
    {
        Livewire::test(DailySurah::class)
            ->assertSee('Oleh: Imam al-Haddad');
    }
}
