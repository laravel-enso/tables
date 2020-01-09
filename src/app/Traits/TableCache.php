<?php

namespace LaravelEnso\Tables\App\Traits;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

trait TableCache
{
    public static function bootTableCache()
    {
        self::created(fn ($model) => $model->resetTableCache());

        self::deleted(fn ($model) => $model->resetTableCache());
    }

    public function resetTableCache()
    {
        $key = config('enso.tables.cache.prefix').':'.$this->getTable();

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags($key)->flush();

            return;
        }

        Cache::forget($key);
    }
}
