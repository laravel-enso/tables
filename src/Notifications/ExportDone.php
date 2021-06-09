<?php

namespace LaravelEnso\Tables\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class ExportDone extends Notification implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        protected string $path,
        protected string $filename,
        protected int $entries
    ) {
    }

    public function via()
    {
        return Config::get('enso.tables.export.notifications');
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage($this->toArray() + [
            'level' => 'success',
            'title' => $this->title(),
        ]))->onQueue($this->queue);
    }

    public function toMail($notifiable)
    {
        $appName = Config::get('app.name');

        return (new MailMessage())
            ->subject("[ {$appName} ] {$this->title()}")
            ->markdown('laravel-enso/tables::emails.export', [
                'name' => $notifiable->name,
                'filename' => __($this->filename),
                'entries' => $this->entries,
            ])->attach($this->path);
    }

    public function toArray()
    {
        return [
            'body' => $this->body(),
        ];
    }

    protected function body(): string
    {
        return __('Export emailed: :filename', ['filename' => $this->filename]);
    }

    private function title(): string
    {
        return __('Table export done');
    }
}
