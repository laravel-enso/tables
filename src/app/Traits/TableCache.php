<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Support\Facades\Cache;

trait TableCache
{
    // protected $cachedTable = 'tableId';

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
        if (property_exists($this, 'cachedTable')) {
            Cache::forget('datatable:'.$this->cachedTable);
        }
    }
}
