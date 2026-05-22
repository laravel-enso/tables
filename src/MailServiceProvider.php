<?php

namespace LaravelEnso\Tables;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\Mails\Preview\PreviewDefinition;
use LaravelEnso\Mails\Preview\PreviewRegistry;

class MailServiceProvider extends ServiceProvider
{
    public function boot(PreviewRegistry $registry): void
    {
        $registry->register(new PreviewDefinition(
            key: 'table-export-done',
            name: 'Table Export Done',
            view: 'laravel-enso/tables::emails.export',
            data: [
                'name' => 'Jane',
                'filename' => 'users-export.xlsx',
                'entries' => 1284,
                'url' => 'https://example.com/files/users-export.xlsx',
            ],
            section: PreviewDefinition::Core,
        ));
    }
}
