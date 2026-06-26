<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KontribusiDisetujui extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $entityLabel,
        public readonly int $points,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kontribusi Anda Disetujui — Syaikhuna')
            ->greeting('Assalamualaikum, ' . $notifiable->name)
            ->line("Kontribusi Anda untuk **{$this->entityLabel}** telah disetujui oleh admin.")
            ->line("Anda mendapatkan **{$this->points} poin XP Khidmah**.")
            ->action('Lihat Dashboard Kontributor', url('/kontributor/saya'))
            ->salutation('Jazakallahu khairan,');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'kontribusi_disetujui',
            'message' => "Kontribusi «{$this->entityLabel}» Anda disetujui. +{$this->points} XP",
            'points' => $this->points,
            'entity_label' => $this->entityLabel,
            'url' => '/kontributor/saya',
        ];
    }
}
