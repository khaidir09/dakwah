<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RewardKlaimDiterima extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rupiah = 'Rp '.number_format($this->amount, 0, ',', '.');

        return (new MailMessage)
            ->subject('Pengajuan Klaim Reward Diterima — Syaikhuna')
            ->greeting('Assalamualaikum, '.$notifiable->name)
            ->line("Pengajuan klaim reward Anda sebesar **{$rupiah}** telah kami terima dan sedang menunggu diproses oleh admin.")
            ->line('Anda akan mendapat notifikasi kembali setelah reward ditransfer.')
            ->action('Lihat Status Klaim', url('/kontributor/saya'))
            ->salutation('Jazakallahu khairan,');
    }

    public function toArray(object $notifiable): array
    {
        $rupiah = 'Rp '.number_format($this->amount, 0, ',', '.');

        return [
            'type' => 'reward_klaim_diterima',
            'message' => "Pengajuan klaim reward {$rupiah} Anda sedang diproses.",
            'amount' => $this->amount,
            'url' => '/kontributor/saya',
        ];
    }
}
