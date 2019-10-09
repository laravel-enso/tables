<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use ReflectionClass;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\app\Traits\TableCache;
use LaravelEnso\Tables\app\Contracts\RawTotal;
use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Services\Table\Filters;

class Meta
{
    private $config;
    private $query;
    private $filters;
    private $count;
    private $filtered;
    private $total;
    private $fullRecordInfo;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->query = $config->table()->query();
        $this->total = collect();
        $this->filters = false;
    }

    public function data()
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

    private function build()
    {
        $this->setCount()
            ->filter()
            ->setDetailedInfo()
            ->countFiltered()
            ->setTotal();
    }

    private function setCount()
    {
        $this->filtered = $this->count = $this->cachedCount();

        return $this;
    }

    private function filter()
    {
        $this->filters = (new Filters($this->config, $this->query))
            ->handle();

        return $this;
    }

    private function setDetailedInfo()
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

    private function setTotal()
    {
        $this->config->columns()
            ->filter(function ($column) {
                return $column->get('meta')->get('total');
            })->each(function ($column) {
                $this->total[$column->get('name')] = $this->config->table() instanceof RawTotal
                    ? $this->config->table()->rawTotal($column)
                    : $this->query->sum($column->get('data'));

                if ($column->get('meta')->get('cents')) {
                    $this->total[$column->get('name')] /= 100;
                }
            });

        return $this;
    }

    private function cachedCount()
    {
        return $this->shouldCache()
            ? Cache::remember($this->cacheKey(), now()->addHour(), function () {
                return $this->count();
            }) : $this->count();
    }

    private function cacheKey()
    {
        return config('enso.tables.cache.prefix')
            .':'.$this->query->getModel()->getTable();
    }

    private function shouldCache()
    {
        if ($this->config->has('countCache')) {
            return $this->config->get('countCache');
        }

        if (config('enso.tables.cache.count')) {
            $reflection = new ReflectionClass($this->query->getModel());

            return collect($reflection->getTraits())->has(TableCache::class);
        }

        return false;
    }
}
