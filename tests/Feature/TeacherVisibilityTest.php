<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Kontributor', 'guard_name' => 'web']);
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    }

    private function guru(array $attributes = []): Teacher
    {
        return Teacher::create(array_merge([
            'name' => 'Guru Uji',
            'slug' => 'guru-uji',
            'biografi' => '<p>Biografi</p>',
        ], $attributes));
    }

    private function kontributor(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Kontributor');

        return $user;
    }

    /** @test */
    public function publik_tidak_dapat_membuka_manaqib_pending(): void
    {
        $guru = $this->guru([
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('manaqib-detail', $guru->slug))->assertNotFound();
    }

    /** @test */
    public function publik_tidak_dapat_membuka_manaqib_rejected(): void
    {
        $guru = $this->guru([
            'contribution_status' => 'rejected',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('manaqib-detail', $guru->slug))->assertNotFound();
    }

    /** @test */
    public function publik_tidak_dapat_membuka_guru_pending(): void
    {
        $guru = $this->guru([
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('guru-detail', $guru->slug))->assertNotFound();
    }

    /** @test */
    public function pemilik_dapat_melihat_manaqib_pending_miliknya(): void
    {
        $pemilik = $this->kontributor();
        $guru = $this->guru([
            'contribution_status' => 'pending',
            'contributor_user_id' => $pemilik->id,
        ]);

        $this->actingAs($pemilik)
            ->get(route('manaqib-detail', $guru->slug))
            ->assertOk()
            ->assertSee('menunggu moderasi admin');
    }

    /** @test */
    public function kontributor_lain_tidak_dapat_melihat_manaqib_pending(): void
    {
        $pemilik = $this->kontributor();
        $orangLain = $this->kontributor();

        $guru = $this->guru([
            'contribution_status' => 'pending',
            'contributor_user_id' => $pemilik->id,
        ]);

        $this->actingAs($orangLain)
            ->get(route('manaqib-detail', $guru->slug))
            ->assertNotFound();
    }

    /** @test */
    public function super_admin_dapat_melihat_manaqib_pending(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Super Admin');

        $guru = $this->guru([
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->actingAs($admin)
            ->get(route('manaqib-detail', $guru->slug))
            ->assertOk();
    }

    /** @test */
    public function manaqib_approved_tetap_dapat_dibuka_publik(): void
    {
        $guru = $this->guru([
            'contribution_status' => 'approved',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('manaqib-detail', $guru->slug))->assertOk();
        $this->get(route('guru-detail', $guru->slug))->assertOk();
    }

    /** @test */
    public function manaqib_legacy_tanpa_contribution_status_tetap_publik(): void
    {
        $guru = $this->guru();

        $this->get(route('manaqib-detail', $guru->slug))->assertOk();
        $this->get(route('guru-detail', $guru->slug))->assertOk();
    }

    /** @test */
    public function foto_bersama_tampil_di_halaman_publik(): void
    {
        $pemilik = $this->kontributor();
        $guru = $this->guru([
            'contribution_status' => 'approved',
            'contributor_user_id' => $pemilik->id,
            'foto_bersama' => 'guru/bersama/foto.webp',
            'foto_bersama_caption' => 'Bersama beliau di Sekumpul, 2004',
        ]);

        $this->get(route('manaqib-detail', $guru->slug))
            ->assertOk()
            ->assertSee('Bersama beliau di Sekumpul, 2004')
            ->assertSee('guru/bersama/foto.webp', false);

        $this->get(route('guru-detail', $guru->slug))
            ->assertOk()
            ->assertSee('Bersama beliau di Sekumpul, 2004');
    }
}
