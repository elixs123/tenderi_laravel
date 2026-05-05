<?php 
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewTenderDetected extends Notification
{
    use Queueable;

    public $procedure;

    public function __construct($procedure)
    {
        $this->procedure = $procedure;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('/tender-progress/' . $this->procedure->id);

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('NOVI TENDER JE DETEKTOVAN: JP ELEKTROPRIVREDA BIH D.D. SARAJEVO')
            ->view('emails.tenders.upravatender', [
                'procedure' => $this->procedure,
                'url' => $url
            ]);
    }
}