<?php

namespace LaravelEnso\Tables\app\Traits;

use Illuminate\Support\Facades\Cache;

trait TableCache
{
    protected static function bootTableCache()
    {
        self::created(fn($model) => $model->resetTableCache());

        self::deleted(fn($model) => $model->resetTableCache());
    }

    public function resetTableCache()
    {
        Cache::forget(
            config('enso.tables.cache.prefix').':'.$this->getTable()
        );
    }
}
