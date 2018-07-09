<?php

namespace LaravelEnso\VueDatatable\app\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ExportDoneNotification extends Notification
{
    use Queueable;

    public $file;

    public function __construct(string $file, string $name)
    {
        $this->file = $file;
        $this->name = $name;
    }

    public function via($notifiable)
    {
        return array_merge(['mail'], config('enso.datatable.export.notifications'));
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'level' => 'success',
            'body' => __('Export emailed').': '.__($this->name.' Table'),
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(__(config('app.name')).': '.__('Table Export Notification'))
            ->markdown('laravel-enso/vuedatatable::emails.export', [
                'name' => $notifiable->first_name,
            ])
            ->attach(\Storage::path($this->file));
    }

    public function toArray($notifiable)
    {
        return [
            'body' => __('Export emailed').': '.__($this->name.' Table'),
            'path' => '#',
        ];
    }
}
