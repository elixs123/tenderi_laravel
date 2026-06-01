<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class WeeklyManagementReport extends Notification
{
    use Queueable;

    public function __construct(
        public Collection $accepted,
        public Collection $rejected,
        public Collection $pending,
        public string $weekFrom,
        public string $weekTo,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $total = $this->accepted->count() + $this->rejected->count() + $this->pending->count();

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->from('no-reply@pennyplus.com', 'Izvještaj - Tenderi')
            ->subject("Sedmični izvještaj — {$this->weekFrom} / {$this->weekTo} ({$total} tendera)")
            ->view('emails.management.weekly-report', [
                'accepted' => $this->accepted,
                'rejected' => $this->rejected,
                'pending'  => $this->pending,
                'weekFrom' => $this->weekFrom,
                'weekTo'   => $this->weekTo,
            ]);
    }
}
