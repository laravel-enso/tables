<?php

namespace LaravelEnso\VueDatatable\app\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

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
        if (property_exists($this, 'cachedTable')) {
            cache()->forget('datatable:'.$this->cachedTable);
        };
    }
}
