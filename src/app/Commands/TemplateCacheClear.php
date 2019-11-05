<?php

namespace LaravelEnso\Tables\app\Commands;

use Cache;
use Illuminate\Cache\TaggableStore;
use Illuminate\Console\Command;

class TemplateCacheClear extends Command
{
    protected $signature = 'enso:tables:clear';

    protected $description = 'Clear cached table templates';

    public function handle()
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(config('enso.tables.cache.tag'))->flush();
            $this->info('Enso table cached templates cleared');

            return;
        }

        if ($this->confirm("Your cache driver doesn't support tags, therefore we should flush the whole cache")) {
            Cache::flush();
            $this->info('Application cache cleared');

            return;
        }

        $this->warn('Enso Table cached templates were not cleared');
    }
}
