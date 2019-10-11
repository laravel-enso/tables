<?php

namespace LaravelEnso\Tables\app\Services\Data;

use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Exceptions\FilterException;
use LaravelEnso\Tables\app\Services\Data\Filters\Filter;
use LaravelEnso\Tables\app\Services\Data\Filters\Search;
use LaravelEnso\Tables\app\Contracts\Filter as TableFilter;
use LaravelEnso\Tables\app\Services\Data\Filters\Interval;
use LaravelEnso\Tables\app\Services\Data\Filters\BaseFilter;
use LaravelEnso\Tables\app\Services\Data\Filters\CustomFilter;
use LaravelEnso\Tables\app\Contracts\CustomFilter as TableCustomFilter;

class Filters extends BaseFilter
{
    private static $defaultFilters = [
        Filter::class,
        Interval::class,
        Search::class,
    ];

    private static $customFilters = [
        CustomFilter::class,
    ];

    public function applies(): bool
    {
        return collect(self::$defaultFilters)
            ->merge($this->needsCustomFiltering() ? self::$customFilters : null)
            ->first(function ($filter) {
                return $this->filter($filter)->applies();
            }) !== null;
    }
    
    public function handle(): void
    {
        collect(self::$defaultFilters)
            ->each(function($filter) {
                $this->apply($filter);
            });

        if ($this->needsCustomFiltering()) {
            collect(self::$customFilters)->each(function ($filter) {
                $this->apply($filter);
            });
        }
    }

    public static function filters($filters)
    {
        self::$defaultFilters = $filters;
    }
    
    public static function customFilters($filters)
    {
        self::$customFilters = $filters;
    }

    private function apply($filter)
    {
        $filter = $this->filter($filter);

        if ($filter->applies()) {
            $filter->handle();
        }
    }

    private function filter($filter)
    {
        $instance = App::make($filter, [
            'table' => $this->table,
            'config' => $this->config,
            'query' => $this->query,
        ]);

        if (! $instance instanceof TableFilter) {
            throw FilterException::invalidClass($filter);
        }

        return $instance;
    }

    private function needsCustomFiltering()
    {
        return $this->table instanceof TableCustomFilter;
    }
}
