<?php

namespace Tests\Feature;

use App\Models\PushNotificationLog;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }

    public function test_admin_can_open_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.push-notifications.index'))
            ->assertOk()
            ->assertSee('Kirim Notifikasi');
    }

    public function test_non_admin_cannot_access_or_send(): void
    {
        $this->mock(OneSignalService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('sendToAll');
            $mock->shouldNotReceive('sendNotification');
        });

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.push-notifications.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.push-notifications.send'), [
            'title' => 'Judul',
            'message' => 'Pesan',
            'target' => 'all',
        ])->assertForbidden();

        $this->assertDatabaseCount('push_notification_logs', 0);
    }

    public function test_admin_sends_to_all_subscribers(): void
    {
        $this->mock(OneSignalService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendToAll')
                ->once()
                ->with('Info Majelis', 'Pengajian malam ini diliburkan.', 'https://syaikhuna.test/majelis')
                ->andReturn(['id' => 'abc-123', 'recipients' => 42]);
        });

        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.push-notifications.send'), [
            'title' => 'Info Majelis',
            'message' => 'Pengajian malam ini diliburkan.',
            'url' => 'https://syaikhuna.test/majelis',
            'target' => 'all',
        ])
            ->assertRedirect(route('admin.push-notifications.index'))
            ->assertSessionHas('message', 'Notifikasi terkirim ke 42 penerima.');

        $this->assertDatabaseHas('push_notification_logs', [
            'sent_by' => $admin->id,
            'target' => PushNotificationLog::TARGET_ALL,
            'status' => PushNotificationLog::STATUS_SENT,
            'onesignal_id' => 'abc-123',
            'recipients' => 42,
        ]);
    }

    public function test_admin_sends_to_selected_users(): void
    {
        [$a, $b] = User::factory()->count(2)->create();

        $this->mock(OneSignalService::class, function (MockInterface $mock) use ($a, $b) {
            $mock->shouldReceive('sendNotification')
                ->once()
                ->with('Halo', 'Pesan khusus', [$a->id, $b->id], null)
                ->andReturn(['id' => 'xyz']);
        });

        $this->actingAs($this->admin())->post(route('admin.push-notifications.send'), [
            'title' => 'Halo',
            'message' => 'Pesan khusus',
            'target' => 'users',
            'user_ids' => [$a->id, $b->id],
        ])->assertSessionHas('message', 'Notifikasi terkirim.');

        $log = PushNotificationLog::sole();
        $this->assertSame([$a->id, $b->id], $log->user_ids);
        $this->assertSame(PushNotificationLog::STATUS_SENT, $log->status);
    }

    public function test_selected_users_target_requires_users(): void
    {
        $this->mock(OneSignalService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('sendNotification');
        });

        $this->actingAs($this->admin())->post(route('admin.push-notifications.send'), [
            'title' => 'Halo',
            'message' => 'Pesan',
            'target' => 'users',
        ])->assertSessionHasErrors('user_ids');

        $this->actingAs($this->admin())->post(route('admin.push-notifications.send'), [
            'title' => 'Halo',
            'message' => 'Pesan',
            'target' => 'users',
            'user_ids' => [999999],
        ])->assertSessionHasErrors('user_ids.0');

        $this->assertDatabaseCount('push_notification_logs', 0);
    }

    public function test_failed_send_is_logged_and_reported(): void
    {
        $this->mock(OneSignalService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendToAll')->once()->andReturn(false);
        });

        $this->actingAs($this->admin())
            ->from(route('admin.push-notifications.index'))
            ->post(route('admin.push-notifications.send'), [
                'title' => 'Judul',
                'message' => 'Pesan',
                'target' => 'all',
            ])
            ->assertRedirect(route('admin.push-notifications.index'))
            ->assertSessionHas('error')
            ->assertSessionHasInput('title', 'Judul');

        $this->assertDatabaseHas('push_notification_logs', [
            'status' => PushNotificationLog::STATUS_FAILED,
            'onesignal_id' => null,
        ]);
    }
}
