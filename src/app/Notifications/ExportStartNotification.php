<?php

namespace LaravelEnso\Tables\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ExportStartNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function via()
    {
        return (new Collection(config('enso.tables.export.notifications')))
            ->intersect(['broadcast', 'database'])
            ->toArray();
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
