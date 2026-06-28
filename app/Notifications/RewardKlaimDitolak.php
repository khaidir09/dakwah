<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RewardKlaimDitolak extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $amount,
        public readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rupiah = 'Rp '.number_format($this->amount, 0, ',', '.');

        return (new MailMessage)
            ->subject('Klaim Reward Anda Ditolak — Syaikhuna')
            ->greeting('Assalamualaikum, '.$notifiable->name)
            ->line("Mohon maaf, pengajuan klaim reward Anda sebesar **{$rupiah}** belum dapat kami proses.")
            ->line("**Alasan:** {$this->reason}")
            ->line('Anda dapat memperbaiki data dan mengajukan klaim kembali.')
            ->action('Ajukan Ulang', url('/kontributor/saya'))
            ->salutation('Jazakallahu khairan,');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reward_klaim_ditolak',
            'message' => 'Klaim reward Anda ditolak. '.$this->reason,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'url' => '/kontributor/saya',
        ];
    }
}
