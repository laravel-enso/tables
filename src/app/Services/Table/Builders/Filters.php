<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Table\Builders\Filters\Filter;
use LaravelEnso\Tables\app\Services\Table\Builders\Filters\Search;
use LaravelEnso\Tables\app\Services\Table\Builders\Filters\Interval;
use LaravelEnso\Tables\app\Services\Table\Builders\Filters\BaseFilter;
use LaravelEnso\Tables\app\Services\Table\Builders\Filters\CustomFilter;

class Filters extends BaseFilter
{
    private $custom;

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
            ->merge($this->custom ? self::$customFilters : null)
            ->reduce(function ($isFiltered, $filter) {
                return $this->filter($filter) || $isFiltered;
            }, false);
    }

    public function custom($state)
    {
        $this->custom = $state;

        return $this;
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
            'request' => $this->request,
            'query' => $this->query,
        ])->handle();
    }
}
