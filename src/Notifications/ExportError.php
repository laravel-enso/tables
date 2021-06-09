<?php

namespace LaravelEnso\Tables\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ExportError extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $name)
    {
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
            'level' => 'error',
            'title' => __('Table export error'),
        ]))->onQueue($this->queue);
    }

    public function toArray()
    {
        return [
            'body' => __('The export :name could not be completed due to an unknown error', [
                'name' => $this->name,
            ]),
            'path' => '#',
            'icon' => 'file-excel',
        ];
    }
}
