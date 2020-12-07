<?php

namespace LaravelEnso\Tables\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ExportStarted extends Notification implements ShouldQueue
{
    use Queueable;

    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;

        $this->queue = 'notifications';
    }

    public function via()
    {
        $channels = Config::get('enso.tables.export.notifications');

        return Collection::wrap($channels)
            ->intersect(['broadcast', 'database'])
            ->toArray();
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage($this->toArray() + [
            'level' => 'info',
            'title' => __('Table Export Started'),
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
