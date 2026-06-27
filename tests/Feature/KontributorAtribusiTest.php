<?php

namespace Tests\Feature;

use App\Livewire\ListWirid;
use App\Models\User;
use App\Models\Wirid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KontributorAtribusiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Kontributor', 'guard_name' => 'web']);
    }

    private function makeKontributor(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'username' => 'abdullah',
            'kontributor_since' => now(),
            'badge_title' => 'Penuntut Ilmu',
        ], $attributes));
        $user->assignRole('Kontributor');

        return $user;
    }

    private function makeWirid(array $attributes = []): Wirid
    {
        return Wirid::create(array_merge([
            'nama' => 'Wirid Test',
            'deskripsi' => 'Deskripsi',
            'arab' => 'Arab',
            'arti' => 'Arti',
            'jumlah' => 1,
            'waktu' => 'Pagi',
            'likes' => 0,
            'kategori' => 'wirid',
        ], $attributes));
    }

    /** @test */
    public function profil_publik_menampilkan_identitas_dan_kontribusi(): void
    {
        $kontributor = $this->makeKontributor();
        $this->makeWirid([
            'nama' => 'Wirid Pagi Sejahtera',
            'contributor_user_id' => $kontributor->id,
            'contribution_status' => 'approved',
        ]);

        $this->get(route('kontributor.profil', $kontributor->username))
            ->assertOk()
            ->assertSee($kontributor->name)
            ->assertSee('Penuntut Ilmu')
            ->assertSee('Wirid Pagi Sejahtera');
    }

    /** @test */
    public function profil_404_untuk_non_kontributor(): void
    {
        $biasa = User::factory()->create(['username' => 'orang-biasa', 'kontributor_since' => null]);

        $this->get(route('kontributor.profil', $biasa->username))->assertNotFound();
        $this->get(route('kontributor.profil', 'tidak-ada'))->assertNotFound();
    }

    /** @test */
    public function profil_hanya_menampilkan_kontribusi_tayang_publik(): void
    {
        $kontributor = $this->makeKontributor();
        $this->makeWirid(['nama' => 'Amalan Approved', 'contributor_user_id' => $kontributor->id, 'contribution_status' => 'approved']);
        $this->makeWirid(['nama' => 'Amalan Pending', 'contributor_user_id' => $kontributor->id, 'contribution_status' => 'pending']);

        $this->get(route('kontributor.profil', $kontributor->username))
            ->assertOk()
            ->assertSee('Amalan Approved')
            ->assertDontSee('Amalan Pending');
    }

    /** @test */
    public function kartu_amalan_menampilkan_atribusi_kontributor_dan_fallback_admin(): void
    {
        $kontributor = $this->makeKontributor();
        $this->makeWirid(['nama' => 'Amalan Kontributor', 'contributor_user_id' => $kontributor->id, 'contribution_status' => 'approved']);
        $this->makeWirid(['nama' => 'Amalan Admin', 'contributor_user_id' => null]);

        Livewire::test(ListWirid::class)
            ->assertSee('Amalan Kontributor')
            ->assertSee('Amalan Admin')
            ->assertSee($kontributor->name)
            ->assertSeeHtml('kontributor/profil/'.$kontributor->username)
            ->assertSee('Admin Syaikhuna');
    }

    /** @test */
    public function cta_tampil_untuk_tamu_dan_tersembunyi_untuk_kontributor(): void
    {
        $this->get(route('wirid-list'))
            ->assertOk()
            ->assertSee('Jadi Kontributor');

        $kontributor = $this->makeKontributor();
        $this->actingAs($kontributor)
            ->get(route('wirid-list'))
            ->assertOk()
            ->assertDontSee('Jadi Kontributor');
    }

    /** @test */
    public function daftar_mengisi_username_dan_kontributor_since(): void
    {
        $user = User::factory()->create([
            'name' => 'Hamba Allah',
            'username' => null,
            'kontributor_since' => null,
            'email_verified_at' => now(),
            'phone' => '08123',
            'province_code' => '63',
            'city_code' => '6371',
            'district_code' => '637101',
            'village_code' => '6371011001',
        ]);

        $this->actingAs($user)->post(route('kontributor.daftar'))
            ->assertRedirect(route('kontributor.saya'));

        $user->refresh();
        $this->assertTrue($user->hasRole('Kontributor'));
        $this->assertSame('hamba-allah', $user->username);
        $this->assertNotNull($user->kontributor_since);
    }

    /** @test */
    public function generate_username_unik_saat_nama_sama(): void
    {
        User::factory()->create(['name' => 'Ahmad', 'username' => 'ahmad']);
        $kedua = User::factory()->make(['name' => 'Ahmad', 'username' => null]);
        $kedua->save();

        $this->assertSame('ahmad-1', $kedua->generateUniqueUsername());
    }
}
