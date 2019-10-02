<?php

namespace LaravelEnso\Tables\app\Commands;

use Cache;
use Illuminate\Console\Command;
use Illuminate\Cache\TaggableStore;

class TemplateCacheClear extends Command
{
    protected $signature = 'enso:tables:clear';

    protected $description = 'Clear cached table templates';

    public function handle()
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(config('enso.tables.cache_tags'))
                ->flush();

            $this->info('Table cache tags ('
                .implode(',', config('enso.tables.cache_tags'))
                .') cleared');

            return;
        }

        if ($this->confirm("Your cache driver doesn't support tags, therefore we should flush the whole cache")) {
            Cache::flush();
            $this->info('Application cache cleared');

            return;
        }

        $this->warn('Table cached templates were not cleared');
    }
}
