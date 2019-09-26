<?php

namespace LaravelEnso\Tables\app\Commands;

use Cache;
use Illuminate\Console\Command;
use Illuminate\Cache\TaggableStore;

class TemplateCacheClear extends Command
{
    protected $signature = 'enso:table:clear';

    protected $description = 'Clear cached templates';

    public function handle()
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(config('enso.tables.cache_tags'))
                ->flush();

            $this->info('table cache tags ('
                . implode(',', config('enso.tables.cache_tags'))
                .') cleared');

            return;
        }

        if($this->confirm("Your cache driver doesn't support tags, therefore we should flush the whole cache")) {
            Cache::flush();
            $this->info('table cache cleared');

            return;
        }

        $this->warn('cache clear ignored');
    }
}
