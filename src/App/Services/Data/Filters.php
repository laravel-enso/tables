<?php

namespace LaravelEnso\Tables\App\Services\Data;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\App\Contracts\CustomFilter as TableCustomFilter;
use LaravelEnso\Tables\App\Contracts\Filter as TableFilter;
use LaravelEnso\Tables\App\Exceptions\Filter as Exception;
use LaravelEnso\Tables\App\Services\Data\Filters\BaseFilter;
use LaravelEnso\Tables\App\Services\Data\Filters\CustomFilter;
use LaravelEnso\Tables\App\Services\Data\Filters\Filter;
use LaravelEnso\Tables\App\Services\Data\Filters\Interval;
use LaravelEnso\Tables\App\Services\Data\Filters\Search;

class Filters extends BaseFilter
{
    private static array $defaultFilters = [
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
            throw Exception::invalidClass($filter);
        }

        return $instance;
    }

    private function needsCustomFiltering(): bool
    {
        return $this->table instanceof TableCustomFilter;
    }

    private function applicable(): Collection
    {
        return (new Collection(self::$defaultFilters))
            ->merge($this->needsCustomFiltering() ? self::$customFilters : null);
    }
}
