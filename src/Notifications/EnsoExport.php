<?php

namespace LaravelEnso\Tables\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class EnsoExport extends Notification implements ShouldQueue
{
    use Dispatchable, Queueable;

    private string $path;
    private string $filename;
    private $dataExport;
    private $entries;

    public function __construct(string $path, string $filename, $dataExport, $entries)
    {
        $this->path = $path;
        $this->filename = $filename;
        $this->dataExport = $dataExport;
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
            'title' => __('Table Export Done'),
        ]))->onQueue($this->queue);
    }

    public function toMail($notifiable)
    {
        $appName = Config::get('app.name');
        $title = __('Table Export Notification');

        $mail = (new MailMessage())
            ->subject("[ {$appName} ] $title")
            ->markdown('laravel-enso/tables::emails.export', [
                'name' => $notifiable->person->appellative(),
                'filename' => __($this->filename),
                'entries' => $this->entries,
                'link' => $this->dataExport
                    ? $this->dataExport->file->temporaryLink()
                    : null,
            ]);

        if (! $this->dataExport) {
            $mail->attach($this->path);
        }

        return $mail;
    }

    public function toArray()
    {
        return [
            'body' => $this->dataExport
                ? __('Export available for download').': '.__($this->filename)
                : __('Export emailed').': '.__($this->filename),
            'icon' => 'file-excel',
            'path' => $this->dataExport ? '/files' : null,
        ];
    }
}
