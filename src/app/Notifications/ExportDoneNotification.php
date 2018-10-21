<?php

namespace LaravelEnso\VueDatatable\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ExportDoneNotification extends Notification
{
    use Queueable;

    public $filePath;
    public $filename;
    public $link;

    public function __construct(string $filePath, string $filename, string $link = null)
    {
        $this->filePath = $filePath;
        $this->filename = $filename;
        $this->link = $link;
    }

    public function via($notifiable)
    {
        return array_merge(['mail'], config('enso.datatable.export.notifications'));
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'level' => 'success',
            'title' => __('Export Done'),
            'body' => $this->link
                ? __('Export available for download').': '.__($this->filename)
                : __('Export emailed').': '.__($this->filename),
            'icon' => 'file-excel',
        ]);
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage())
            ->subject(__(config('app.name')).': '.__('Table Export Notification'))
            ->markdown('laravel-enso/vuedatatable::emails.export', [
                'name' => $notifiable->person->appellative
                    ?: $notifiable->person->name,
                'filename' => __($this->filename),
                'link' => $this->link,
            ]);

        if (! $this->link) {
            $mail->attach(\Storage::path($this->filePath));
        }

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'body' => $this->link
                ? __('Export available for download').': '.__($this->filename)
                : __('Export emailed').': '.__($this->filename),
            'icon' => 'file-excel',
            'path' => $this->link
                ? '/files'
                : null,
        ];
    }
}
