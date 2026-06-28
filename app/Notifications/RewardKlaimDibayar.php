<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class RewardKlaimDibayar extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $amount,
        public readonly ?Carbon $transferredAt = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rupiah = 'Rp '.number_format($this->amount, 0, ',', '.');

        $mail = (new MailMessage)
            ->subject('Reward Anda Telah Ditransfer — Syaikhuna')
            ->greeting('Assalamualaikum, '.$notifiable->name)
            ->line("Alhamdulillah! Reward kontributor sebesar **{$rupiah}** telah ditransfer ke akun e-wallet Anda.");

        if ($this->transferredAt) {
            $mail->line('Tanggal transfer: '.$this->transferredAt->translatedFormat('d F Y'));
        }

        return $mail
            ->line('Terima kasih atas kontribusi Anda untuk Syaikhuna.')
            ->action('Lihat Status Klaim', url('/kontributor/saya'))
            ->salutation('Jazakallahu khairan,');
    }

    public function toArray(object $notifiable): array
    {
        $rupiah = 'Rp '.number_format($this->amount, 0, ',', '.');

        return [
            'type' => 'reward_klaim_dibayar',
            'message' => "Reward {$rupiah} Anda telah ditransfer. Jazakallahu khairan!",
            'amount' => $this->amount,
            'transferred_at' => $this->transferredAt?->toDateString(),
            'url' => '/kontributor/saya',
        ];
    }
}
