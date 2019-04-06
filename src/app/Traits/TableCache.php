<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Support\Facades\Cache;

trait TableCache
{
    protected static function bootTableCache()
    {
        self::created(function ($model) {
            $model->resetTableCache();
        });

        self::deleted(function ($model) {
            $model->resetTableCache();
        });
    }

    public function resetTableCache()
    {
        Cache::forget('datatable:'.$this->getTable());
    }
}
