<?php

namespace Tests\Feature\Kontributor;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BiografiWysiwygTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Kontributor', 'guard_name' => 'web']);
    }

    private function kontributor(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('Kontributor');

        return $user;
    }

    private function kirim(User $user, string $biografi)
    {
        return $this->actingAs($user)->post(route('kontributor.guru.store'), [
            'name' => 'Guru Uji',
            'biografi' => $biografi,
        ]);
    }

    /** @test */
    public function biografi_html_disimpan_setelah_disanitasi(): void
    {
        $user = $this->kontributor();

        $this->kirim($user, '<p>Halo <strong>Guru</strong></p><script>alert(1)</script>')
            ->assertRedirect(route('kontributor.saya'));

        $biografi = Teacher::first()->biografi;

        $this->assertStringContainsString('<strong>Guru</strong>', $biografi);
        $this->assertStringNotContainsString('<script', $biografi);
        $this->assertStringNotContainsString('alert(1)', $biografi);
    }

    /** @test */
    public function atribut_event_handler_dibuang(): void
    {
        $user = $this->kontributor();

        $this->kirim($user, '<p>Foto</p><img src="x" onerror="alert(1)">');

        $biografi = Teacher::first()->biografi;

        $this->assertStringNotContainsString('onerror', $biografi);
    }

    /** @test */
    public function biografi_kosong_dari_editor_ditolak(): void
    {
        $user = $this->kontributor();

        $this->kirim($user, '<p></p>')->assertSessionHasErrors('biografi');

        $this->assertSame(0, Teacher::count());
    }

    /** @test */
    public function biografi_dengan_hanya_spasi_ditolak(): void
    {
        $user = $this->kontributor();

        $this->kirim($user, '<p>   </p>')->assertSessionHasErrors('biografi');

        $this->assertSame(0, Teacher::count());
    }

    /** @test */
    public function biografi_dengan_daftar_dan_tautan_tersimpan_utuh(): void
    {
        $user = $this->kontributor();

        $this->kirim($user, '<p>Karya beliau:</p><ul><li>Kitab A</li></ul><p><a href="https://contoh.test">Sumber</a></p>');

        $biografi = Teacher::first()->biografi;

        $this->assertStringContainsString('<ul>', $biografi);
        $this->assertStringContainsString('<li>Kitab A</li>', $biografi);
        $this->assertStringContainsString('href="https://contoh.test"', $biografi);
    }

    /** @test */
    public function form_kontributor_merender_editor_wysiwyg(): void
    {
        $user = $this->kontributor();

        $this->actingAs($user)
            ->get(route('kontributor.guru.create'))
            ->assertOk()
            ->assertSee('id="biografi-editor"', false)
            ->assertSee('@tiptap/core', false);
    }
}
