<?php

namespace LaravelEnso\VueDatatable\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ExportStartNotification extends Notification
{
    use Queueable;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function via($notifiable)
    {
        return config('enso.datatable.export.notifications');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'level' => 'info',
            'body' => __('Export started').': '.__($this->name.' Table'),
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'body' => __('Export started').': '.__($this->name.' Table'),
            'link' => '#',
        ];
    }
}
