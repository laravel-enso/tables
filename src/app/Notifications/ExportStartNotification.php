<?php

namespace LaravelEnso\VueDatatable\app\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ExportStartNotification extends Notification
{
    private $exportName;

    public function __construct(string $exportName)
    {
        $this->exportName = $exportName;
    }

    public function via($notifiable)
    {
        return config('enso.datatable.export.notifications');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'level' => 'info',
            'title' => __('Export Started'),
            'body' => __('Export started').': '.__($this->exportName),
            'icon' => 'file-excel',
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'body' => __('Export started').': '.__($this->exportName),
            'path' => '#',
            'icon' => 'file-excel',
        ];
    }
}
