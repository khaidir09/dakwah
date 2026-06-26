<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KontribusiDitolak extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $entityLabel,
        public readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kontribusi Anda Perlu Diperbaiki — Syaikhuna')
            ->greeting('Assalamualaikum, ' . $notifiable->name)
            ->line("Kontribusi Anda untuk **{$this->entityLabel}** memerlukan perbaikan.")
            ->line("**Alasan:** {$this->reason}")
            ->line('Silakan edit dan kirim ulang kontribusi Anda.')
            ->action('Edit Kontribusi', url('/kontributor/saya'))
            ->salutation('Jazakallahu khairan,');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'kontribusi_ditolak',
            'message' => "Kontribusi «{$this->entityLabel}» perlu diperbaiki.",
            'reason' => $this->reason,
            'entity_label' => $this->entityLabel,
            'url' => '/kontributor/saya',
        ];
    }
}
