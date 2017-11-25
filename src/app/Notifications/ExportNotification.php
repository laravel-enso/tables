<?php

namespace LaravelEnso\VueDatatable\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportNotification extends Notification
{
    use Queueable;

    public $file;

    public function __construct(string $file, string $name)
    {
        $this->file = $file;
        $this->name = $name;
    }

    public function via($notifiable)
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'body' => 'export notification',
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->line(__('You will find attached the requested report.'))
            ->line(__('Thank you for using our application!'))
            ->attach($this->file);
    }

    public function toArray($notifiable)
    {
        return [
            'body' => 'Export emailed: '.$this->name.' Table',
            'link' => '#',
        ];
    }
}
