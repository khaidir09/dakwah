<?php

namespace Tests\Feature;

use App\Livewire\User\AcaraMajelis;
use App\Models\Assembly;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AcaraMajelisDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function pemilikMajelis(string $namaMajelis): array
    {
        $user = User::factory()->create();

        $assembly = Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => $namaMajelis,
            'deskripsi' => 'Deskripsi majelis uji.',
            'alamat' => 'Jl. Uji No. 1, Banjarmasin',
            'guru' => 'Guru Uji',
            'maps' => 'https://maps.google.com',
            'status' => 'Aktif',
        ]);

        return [$user, $assembly];
    }

    private function acaraUntuk(Assembly $assembly, string $nama): Event
    {
        return Event::create([
            'assembly_id' => $assembly->id,
            'user_id' => $assembly->user_id,
            'name' => $nama,
            'location' => $assembly->alamat,
            'date' => Carbon::today()->addDays(14)->setTime(19, 30),
            'access' => 'Umum',
            'category' => 'Maulid',
        ]);
    }

    public function test_pemilik_majelis_dapat_menghapus_acaranya_sendiri(): void
    {
        [$pemilik, $majelis] = $this->pemilikMajelis('Majelis Nurul Iman');
        $acara = $this->acaraUntuk($majelis, 'Maulid Akbar');

        Livewire::actingAs($pemilik)
            ->test(AcaraMajelis::class)
            ->call('confirmDelete', $acara->id)
            ->call('deleteEvent');

        $this->assertDatabaseMissing('events', ['id' => $acara->id]);
    }

    public function test_pengguna_tidak_dapat_menghapus_acara_majelis_lain(): void
    {
        [, $majelisKorban] = $this->pemilikMajelis('Majelis Korban');
        $acaraKorban = $this->acaraUntuk($majelisKorban, 'Haul Akbar');

        [$penyerang] = $this->pemilikMajelis('Majelis Penyerang');

        Livewire::actingAs($penyerang)
            ->test(AcaraMajelis::class)
            ->call('confirmDelete', $acaraKorban->id)
            ->call('deleteEvent');

        $this->assertDatabaseHas('events', ['id' => $acaraKorban->id]);
    }

    public function test_pengguna_tanpa_majelis_tidak_dapat_menghapus_acara_siapa_pun(): void
    {
        [, $majelisKorban] = $this->pemilikMajelis('Majelis Korban');
        $acaraKorban = $this->acaraUntuk($majelisKorban, 'Haul Akbar');

        Livewire::actingAs(User::factory()->create())
            ->test(AcaraMajelis::class)
            ->call('confirmDelete', $acaraKorban->id)
            ->call('deleteEvent');

        $this->assertDatabaseHas('events', ['id' => $acaraKorban->id]);
    }

    public function test_daftar_hanya_memuat_acara_milik_sendiri(): void
    {
        [, $majelisLain] = $this->pemilikMajelis('Majelis Lain');
        $this->acaraUntuk($majelisLain, 'Acara Majelis Lain');

        [$pemilik, $majelisSaya] = $this->pemilikMajelis('Majelis Saya');
        $this->acaraUntuk($majelisSaya, 'Acara Majelis Saya');

        Livewire::actingAs($pemilik)
            ->test(AcaraMajelis::class)
            ->assertSee('Acara Majelis Saya')
            ->assertDontSee('Acara Majelis Lain')
            ->assertViewHas('events_count', 1);
    }
}
