<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\KontribusiXpSetting;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\KontribusiDisetujui;
use App\Services\KhidmahService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KhidmahXpTest extends TestCase
{
    use RefreshDatabase;

    private KhidmahService $khidmah;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Role::create(['name' => 'Kontributor', 'guard_name' => 'web']);
        KontribusiXpSetting::create(['contribution_type' => 'guru', 'points' => 10, 'label' => 'Guru']);

        $this->khidmah = app(KhidmahService::class);
    }

    private function kontributor(): User
    {
        $user = User::factory()->create(['total_khidmah_points' => 0]);
        $user->assignRole('Kontributor');

        return $user;
    }

    private function guruPending(User $user): Teacher
    {
        $guru = Teacher::create([
            'name' => 'Guru Uji',
            'slug' => 'guru-uji',
            'biografi' => '<p>Biografi</p>',
            'contributor_user_id' => $user->id,
            'contribution_status' => 'pending',
        ]);

        Contribution::create([
            'user_id' => $user->id,
            'contributable_id' => $guru->id,
            'contributable_type' => Teacher::class,
            'points_earned' => 0,
        ]);

        return $guru;
    }

    /** @test */
    public function approve_memberi_xp_sekali(): void
    {
        $user = $this->kontributor();
        $guru = $this->guruPending($user);

        $this->khidmah->approve($guru, 'guru', $guru->name);

        $this->assertSame(10, $user->fresh()->total_khidmah_points);
        $this->assertSame('approved', $guru->fresh()->contribution_status);
        Notification::assertSentToTimes($user, KontribusiDisetujui::class, 1);
    }

    /** @test */
    public function approve_ulang_setelah_edit_tidak_menambah_xp(): void
    {
        $user = $this->kontributor();
        $guru = $this->guruPending($user);

        $this->khidmah->approve($guru, 'guru', $guru->name);

        // Kontributor menyunting foto bersamanya → kembali ke antrean moderasi.
        $guru->update(['contribution_status' => 'pending']);

        $this->khidmah->approve($guru->fresh(), 'guru', $guru->name);

        $this->assertSame(10, $user->fresh()->total_khidmah_points);
        $this->assertSame('approved', $guru->fresh()->contribution_status);
        Notification::assertSentToTimes($user, KontribusiDisetujui::class, 1);
    }

    /** @test */
    public function approve_setelah_revoke_memberi_xp_lagi(): void
    {
        $user = $this->kontributor();
        $guru = $this->guruPending($user);

        $this->khidmah->approve($guru, 'guru', $guru->name);
        $this->assertSame(10, $user->fresh()->total_khidmah_points);

        $this->khidmah->revoke($guru->fresh(), 'guru', $guru->name, 'Foto tidak sesuai');
        $this->assertSame(0, $user->fresh()->total_khidmah_points);

        $guru->update(['contribution_status' => 'pending']);
        $this->khidmah->approve($guru->fresh(), 'guru', $guru->name);

        $this->assertSame(10, $user->fresh()->total_khidmah_points);
    }
}
