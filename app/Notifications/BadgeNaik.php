<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BadgeNaik extends Notification
{
    use Queueable;

    public function __construct(public readonly string $badgeBaru) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Selamat! Anda Naik ke Gelar {$this->badgeBaru} — Syaikhuna")
            ->greeting('Assalamualaikum, ' . $notifiable->name)
            ->line("Alhamdulillah! Kontribusi Anda telah mengantarkan Anda ke gelar baru:")
            ->line("**{$this->badgeBaru}**")
            ->action('Lihat Profil Kontributor', url('/kontributor/saya'))
            ->salutation('Jazakallahu khairan,');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'badge_naik',
            'message' => "Selamat! Anda meraih gelar baru: {$this->badgeBaru}",
            'badge' => $this->badgeBaru,
            'url' => '/kontributor/saya',
        ];
    }
}
