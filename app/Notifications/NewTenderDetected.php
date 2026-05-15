<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class NewTenderDetected extends Notification
{
    use Queueable;

    public function __construct(public Collection $procedures) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $count = $this->procedures->count();

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("Novi tenderi ({$count}) — " . now()->format('d.m.Y H:i'))
            ->view('emails.tenders.novi-tender-sync', [
                'procedures' => $this->procedures,
            ]);
    }
}
