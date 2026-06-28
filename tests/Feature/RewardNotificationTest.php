<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RewardKlaimDibayar;
use App\Notifications\RewardKlaimDiterima;
use App\Notifications\RewardKlaimDitolak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RewardNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ketiga_notifikasi_terkirim_via_database_dan_mail(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $user->notify(new RewardKlaimDiterima(50000));
        $user->notify(new RewardKlaimDibayar(50000, now()));
        $user->notify(new RewardKlaimDitolak(50000, 'Nomor e-wallet tidak valid'));

        Notification::assertSentTo($user, RewardKlaimDiterima::class);
        Notification::assertSentTo($user, RewardKlaimDibayar::class);
        Notification::assertSentTo($user, RewardKlaimDitolak::class);
    }

    public function test_payload_dan_render_mail_tidak_error(): void
    {
        $user = User::factory()->create();

        $diterima = new RewardKlaimDiterima(50000);
        $dibayar = new RewardKlaimDibayar(50000, now());
        $ditolak = new RewardKlaimDitolak(50000, 'Data tidak sesuai');

        foreach ([$diterima, $dibayar, $ditolak] as $notification) {
            $this->assertSame(['database', 'mail'], $notification->via($user));
            $this->assertArrayHasKey('message', $notification->toArray($user));
            $this->assertNotEmpty($notification->toMail($user)->subject);
        }

        $this->assertSame(50000, $diterima->toArray($user)['amount']);
        $this->assertSame('Data tidak sesuai', $ditolak->toArray($user)['reason']);
    }
}
