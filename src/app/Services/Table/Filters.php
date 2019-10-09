<?php

namespace LaravelEnso\Tables\app\Services\Table;

use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Table\Filters\Filter;
use LaravelEnso\Tables\app\Services\Table\Filters\Search;
use LaravelEnso\Tables\app\Services\Table\Filters\Interval;
use LaravelEnso\Tables\app\Services\Table\Filters\BaseFilter;
use LaravelEnso\Tables\app\Services\Table\Filters\CustomFilter;
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

    public function handle(): bool
    {
        return collect(self::$defaultFilters)
            ->merge($this->custom() ? self::$customFilters : null)
            ->reduce(function ($isFiltered, $filter) {
                return $this->filter($filter) || $isFiltered;
            }, false);
    }

    private function custom()
    {
        return $this->config->table() instanceof TableCustomFilter;
    }

    public static function filters($filters)
    {
        self::$defaultFilters = $filters;
    }

    public static function customFilters($filters)
    {
        self::$customFilters = $filters;
    }

    private function filter($filter)
    {
        return App::make($filter, [
            'config' => $this->config,
            'query' => $this->query,
        ])->handle();
    }
}
