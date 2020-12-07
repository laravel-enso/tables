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

    protected string $path;
    protected string $filename;
    protected int $entries;

    public function __construct(string $path, string $filename, int $entries)
    {
        $this->path = $path;
        $this->filename = $filename;
        $this->entries = $entries;
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

        $mail = (new MailMessage())
            ->subject("[ {$appName} ] {$this->title()}")
            ->markdown('laravel-enso/tables::emails.export', [
                'name' => $this->notifiable($notifiable),
                'filename' => __($this->filename),
                'entries' => $this->entries,
                'link' => $this->link(),
            ]);

        if (! $this->link()) {
            $mail->attach($this->path);
        }

        return $mail;
    }

    public function toArray()
    {
        return [
            'body' => $this->body(),
            'icon' => 'file-excel',
            'path' => '/import',
        ];
    }

    protected function notifiable($notifiable): string
    {
        return $notifiable->name;
    }

    protected function body(): string
    {
        return __('Export emailed: :filename', ['filename' => $this->filename]);
    }

    protected function link(): ?string
    {
        return null;
    }

    private function title(): string
    {
        return __('Table export done');
    }
}
