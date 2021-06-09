<?php

namespace LaravelEnso\Tables\Services\Data;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\Contracts\CustomFilter as TableCustomFilter;
use LaravelEnso\Tables\Contracts\Filter as TableFilter;
use LaravelEnso\Tables\Exceptions\Filter as Exception;
use LaravelEnso\Tables\Services\Data\Filters\BaseFilter;
use LaravelEnso\Tables\Services\Data\Filters\CustomFilter;
use LaravelEnso\Tables\Services\Data\Filters\Filter;
use LaravelEnso\Tables\Services\Data\Filters\Interval;
use LaravelEnso\Tables\Services\Data\Filters\Search;
use LaravelEnso\Tables\Services\Data\Filters\Searches;

class Filters extends BaseFilter
{
    private static array $defaultFilters = [
        Searches::class,
        Filter::class,
        Interval::class,
        Search::class,
    ];

    private static array $customFilters = [
        CustomFilter::class,
    ];

    public function applies(): bool
    {
        return $this->applicable()
            ->first(fn ($filter) => $this->filter($filter)->applies()) !== null;
    }

    public function handle(): void
    {
        $this->applicable()->each(fn ($filter) => $this->apply($filter));
    }

    public static function filters($filters): void
    {
        self::$defaultFilters = $filters;
    }

    public static function customFilters($filters): void
    {
        self::$customFilters = $filters;
    }

    private function apply($filter): void
    {
        $filter = $this->filter($filter);

        if ($filter->applies()) {
            $filter->handle();
        }
    }

    private function filter($filter): TableFilter
    {
        $instance = App::make($filter, [
            'table' => $this->table,
            'config' => $this->config,
            'query' => $this->query,
        ]);

        if (! $instance instanceof TableFilter) {
            throw Exception::missingContract($filter);
        }

        return $instance;
    }

    private function needsCustomFiltering(): bool
    {
        return $this->table instanceof TableCustomFilter;
    }

    private function applicable(): Collection
    {
        return Collection::wrap(self::$defaultFilters)
            ->merge($this->needsCustomFiltering() ? self::$customFilters : null);
    }
}
