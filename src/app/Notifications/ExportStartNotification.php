<?php

namespace LaravelEnso\Tables\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ExportStartNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function via()
    {
        return config('enso.tables.export.notifications');
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage([
            'level' => 'info',
            'title' => __('Export Started'),
            'body' => __('Export started').': '.__($this->name),
            'icon' => 'file-excel',
        ]))->onQueue($this->queue);
    }

    public function toArray()
    {
        return [
            'body' => __('Export started').': '.__($this->name),
            'path' => '#',
            'icon' => 'file-excel',
        ];
    }
}
