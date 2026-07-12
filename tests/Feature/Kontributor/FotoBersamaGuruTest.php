<?php

namespace Tests\Feature\Kontributor;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FotoBersamaGuruTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Role::create(['name' => 'Kontributor', 'guard_name' => 'web']);
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    }

    private function kontributor(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Kontributor');

        return $user;
    }

    private function guru(User $pemilik, array $attributes = []): Teacher
    {
        return Teacher::create(array_merge([
            'name' => 'Guru Uji',
            'slug' => 'guru-uji',
            'biografi' => '<p>Biografi</p>',
            'contributor_user_id' => $pemilik->id,
            'contribution_status' => 'pending',
        ], $attributes));
    }

    private function dataValid(array $override = []): array
    {
        return array_merge([
            'name' => 'Guru Uji',
            'biografi' => '<p>Biografi guru.</p>',
        ], $override);
    }

    /** @test */
    public function kontributor_dapat_mengunggah_foto_bersama_dengan_caption(): void
    {
        $user = $this->kontributor();

        $this->actingAs($user)
            ->post(route('kontributor.guru.store'), $this->dataValid([
                'foto_bersama' => UploadedFile::fake()->image('bersama.jpg', 2400, 1200),
                'foto_bersama_caption' => 'Bersama beliau di Sekumpul, 2004',
            ]))
            ->assertRedirect(route('kontributor.saya'));

        $guru = Teacher::first();

        $this->assertNotNull($guru->foto_bersama);
        $this->assertSame('Bersama beliau di Sekumpul, 2004', $guru->foto_bersama_caption);
        $this->assertSame('pending', $guru->contribution_status);
        Storage::disk('public')->assertExists($guru->foto_bersama);
        $this->assertStringStartsWith('guru/bersama/', $guru->foto_bersama);
    }

    /** @test */
    public function caption_wajib_saat_foto_diunggah(): void
    {
        $user = $this->kontributor();

        $this->actingAs($user)
            ->post(route('kontributor.guru.store'), $this->dataValid([
                'foto_bersama' => UploadedFile::fake()->image('bersama.jpg'),
            ]))
            ->assertSessionHasErrors('foto_bersama_caption');

        $this->assertSame(0, Teacher::count());
    }

    /** @test */
    public function caption_wajib_saat_foto_lama_masih_ada(): void
    {
        $user = $this->kontributor();
        $guru = $this->guru($user, [
            'foto_bersama' => 'guru/bersama/lama.webp',
            'foto_bersama_caption' => 'Caption lama',
        ]);

        $this->actingAs($user)
            ->put(route('kontributor.guru.update', $guru->id), $this->dataValid([
                'foto_bersama_caption' => '',
            ]))
            ->assertSessionHasErrors('foto_bersama_caption');
    }

    /** @test */
    public function foto_melebihi_8mb_ditolak(): void
    {
        $user = $this->kontributor();

        $this->actingAs($user)
            ->post(route('kontributor.guru.store'), $this->dataValid([
                'foto_bersama' => UploadedFile::fake()->image('besar.jpg')->size(9000),
                'foto_bersama_caption' => 'Caption',
            ]))
            ->assertSessionHasErrors('foto_bersama');
    }

    /** @test */
    public function mengganti_foto_pada_manaqib_approved_mengembalikan_status_ke_pending(): void
    {
        $user = $this->kontributor();
        Storage::disk('public')->put('guru/bersama/lama.webp', 'lama');

        $guru = $this->guru($user, [
            'contribution_status' => 'approved',
            'foto_bersama' => 'guru/bersama/lama.webp',
            'foto_bersama_caption' => 'Caption lama',
        ]);

        $this->actingAs($user)
            ->put(route('kontributor.guru.update', $guru->id), $this->dataValid([
                'foto_bersama' => UploadedFile::fake()->image('baru.jpg'),
                'foto_bersama_caption' => 'Caption baru',
            ]))
            ->assertRedirect(route('kontributor.saya'));

        $guru->refresh();

        $this->assertSame('pending', $guru->contribution_status);
        $this->assertNotSame('guru/bersama/lama.webp', $guru->foto_bersama);
        Storage::disk('public')->assertMissing('guru/bersama/lama.webp');
        Storage::disk('public')->assertExists($guru->foto_bersama);
    }

    /** @test */
    public function mengubah_caption_saja_pada_manaqib_approved_mengembalikan_status_ke_pending(): void
    {
        $user = $this->kontributor();
        $guru = $this->guru($user, [
            'contribution_status' => 'approved',
            'foto_bersama' => 'guru/bersama/lama.webp',
            'foto_bersama_caption' => 'Caption lama',
        ]);

        $this->actingAs($user)
            ->put(route('kontributor.guru.update', $guru->id), $this->dataValid([
                'foto_bersama_caption' => 'Caption diperbaiki',
            ]));

        $guru->refresh();

        $this->assertSame('pending', $guru->contribution_status);
        $this->assertSame('Caption diperbaiki', $guru->foto_bersama_caption);
        $this->assertSame('guru/bersama/lama.webp', $guru->foto_bersama);
    }

    /** @test */
    public function menghapus_foto_tidak_mengubah_status_approved(): void
    {
        $user = $this->kontributor();
        Storage::disk('public')->put('guru/bersama/lama.webp', 'lama');

        $guru = $this->guru($user, [
            'contribution_status' => 'approved',
            'foto_bersama' => 'guru/bersama/lama.webp',
            'foto_bersama_caption' => 'Caption lama',
        ]);

        $this->actingAs($user)
            ->put(route('kontributor.guru.update', $guru->id), $this->dataValid([
                'hapus_foto_bersama' => '1',
            ]));

        $guru->refresh();

        $this->assertSame('approved', $guru->contribution_status);
        $this->assertNull($guru->foto_bersama);
        $this->assertNull($guru->foto_bersama_caption);
        Storage::disk('public')->assertMissing('guru/bersama/lama.webp');
    }

    /** @test */
    public function menyunting_biografi_saja_tidak_mengubah_status_approved(): void
    {
        $user = $this->kontributor();
        $guru = $this->guru($user, ['contribution_status' => 'approved']);

        $this->actingAs($user)
            ->put(route('kontributor.guru.update', $guru->id), $this->dataValid([
                'biografi' => '<p>Biografi yang diperbarui.</p>',
            ]));

        $guru->refresh();

        $this->assertSame('approved', $guru->contribution_status);
    }

    /** @test */
    public function unggahan_baru_menang_atas_centang_hapus(): void
    {
        $user = $this->kontributor();
        Storage::disk('public')->put('guru/bersama/lama.webp', 'lama');

        $guru = $this->guru($user, [
            'contribution_status' => 'approved',
            'foto_bersama' => 'guru/bersama/lama.webp',
            'foto_bersama_caption' => 'Caption lama',
        ]);

        $this->actingAs($user)
            ->put(route('kontributor.guru.update', $guru->id), $this->dataValid([
                'foto_bersama' => UploadedFile::fake()->image('baru.jpg'),
                'foto_bersama_caption' => 'Caption baru',
                'hapus_foto_bersama' => '1',
            ]));

        $guru->refresh();

        $this->assertNotNull($guru->foto_bersama);
        $this->assertSame('Caption baru', $guru->foto_bersama_caption);
        $this->assertSame('pending', $guru->contribution_status);
        Storage::disk('public')->assertExists($guru->foto_bersama);
    }

    /** @test */
    public function bukan_pemilik_tidak_dapat_mengedit_guru(): void
    {
        $pemilik = $this->kontributor();
        $orangLain = $this->kontributor();
        $guru = $this->guru($pemilik);

        $this->actingAs($orangLain)
            ->put(route('kontributor.guru.update', $guru->id), $this->dataValid([
                'foto_bersama' => UploadedFile::fake()->image('bersama.jpg'),
                'foto_bersama_caption' => 'Caption penyusup',
            ]))
            ->assertNotFound();

        $this->assertNull($guru->fresh()->foto_bersama);
    }

    /** @test */
    public function admin_dapat_menghapus_foto_bersama_tanpa_mengubah_status(): void
    {
        $pemilik = $this->kontributor();
        Storage::disk('public')->put('guru/bersama/lama.webp', 'lama');

        $guru = $this->guru($pemilik, [
            'contribution_status' => 'approved',
            'foto_bersama' => 'guru/bersama/lama.webp',
            'foto_bersama_caption' => 'Caption lama',
        ]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)
            ->delete(route('admin.guru.foto-bersama.destroy', $guru->id))
            ->assertRedirect();

        $guru->refresh();

        $this->assertNull($guru->foto_bersama);
        $this->assertNull($guru->foto_bersama_caption);
        $this->assertSame('approved', $guru->contribution_status);
        Storage::disk('public')->assertMissing('guru/bersama/lama.webp');
    }
}
