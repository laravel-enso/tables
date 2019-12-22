<?php

namespace LaravelEnso\Tables\app\Services\Data\Builders;

use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Exceptions\Cache as Exception;
use LaravelEnso\Tables\app\Services\Data\Config;
use LaravelEnso\Tables\app\Services\Data\Filters;
use ReflectionClass;

class Meta
{
    private $table;
    private $config;
    private $query;
    private $filters;
    private $count;
    private $filtered;
    private $total;
    private $fullRecordInfo;

    public function __construct(Table $table, Config $config)
    {
        $this->table = $table;
        $this->config = $config;
        $this->query = $table->query();
        $this->total = collect();
        $this->filters = false;
    }

    public function build()
    {
        $this->setCount()
            ->filter()
            ->detailedInfo()
            ->countFiltered()
            ->total();

        return $this;
    }

    public function toArray()
    {
        $this->build();

        return [
            'count' => $this->count,
            'filtered' => $this->filtered,
            'total' => $this->total,
            'fullRecordInfo' => $this->fullRecordInfo,
            'filters' => $this->filters,
        ];
    }

    public function count()
    {
        return $this->query->count();
    }

    private function setCount()
    {
        $this->filtered = $this->count = $this->cachedCount();

        return $this;
    }

    private function filter()
    {
        $filters = new Filters(
            $this->table, $this->config, $this->query
        );

        $this->filters = $filters->applies();

        if ($this->filters) {
            $filters->handle();
        }

        return $this;
    }

    private function detailedInfo()
    {
        $this->fullRecordInfo = $this->config->meta()->get('forceInfo')
            || $this->count <= $this->config->meta()->get('fullInfoRecordLimit')
            || ! $this->filters;

        return $this;
    }

    private function countFiltered()
    {
        if ($this->filters && $this->fullRecordInfo) {
            $this->filtered = $this->count();
        }

        return $this;
    }

    private function total()
    {
        if ($this->config->meta()->get('total')) {
            $this->total = (new Total(
                $this->table, $this->config, $this->query
            ))->handle();
        }

        return $this;
    }

    private function cachedCount()
    {
        return $this->shouldCache()
            ? Cache::remember($this->cacheKey(), now()->addHour(), fn() => $this->count())
            : $this->count();
    }

    private function cacheKey()
    {
        return config('enso.tables.cache.prefix')
            .':'.$this->query->getModel()->getTable();
    }

    private function shouldCache()
    {
        $shouldCache = $this->config->has('countCache')
            ? $this->config->get('countCache')
            : config('enso.tables.cache.count');

        if ($shouldCache) {
            $model = $this->query->getModel();

            if (! (new ReflectionClass($model))->hasMethod('resetTableCache')) {
                throw Exception::missingTrait(get_class($model));
            }
        }

        return $shouldCache;
    }
}
