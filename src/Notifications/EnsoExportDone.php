<?php

namespace LaravelEnso\Tables\Notifications;

use LaravelEnso\DataExport\Models\DataExport;

class EnsoExportDone extends ExportDone
{
    private DataExport $export;

    public function __construct(string $path, string $filename, DataExport $export)
    {
        parent::__construct($path, $filename, $export->entries);
        $this->export = $export;
    }

    protected function notifiable($notifiable): string
    {
        return $notifiable->person->appellative();
    }

    protected function body(): string
    {
        return __('Export available for download: :filename', [
            'filename' => $this->filename,
        ]);
    }

    protected function link(): ?string
    {
        return $this->export->file->temporaryLink();
    }
}
