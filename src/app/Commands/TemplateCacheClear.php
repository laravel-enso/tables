<?php

namespace LaravelEnso\Tables\App\Commands;

use Illuminate\Cache\TaggableStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class TemplateCacheClear extends Command
{
    protected $signature = 'enso:tables:clear';

    protected $description = 'Clear cached table templates';

    public function handle()
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('enso.tables.cache.tag'))->flush();
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
