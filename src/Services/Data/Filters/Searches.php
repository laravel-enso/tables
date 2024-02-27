<?php

namespace LaravelEnso\Tables\Services\Data\Filters;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Filters\Enums\ComparisonOperators;
use LaravelEnso\Filters\Enums\SearchModes;
use LaravelEnso\Filters\Services\Search;
use LaravelEnso\Helpers\Services\Obj;

class Searches extends BaseFilter
{
    public function applies(): bool
    {
        return $this->filterable()->isNotEmpty()
            && $this->filters()->isNotEmpty();
    }

    public function handle(): void
    {
        $this->query->where(fn () => $this->filters()
            ->each(fn ($filter) => $this->filter($filter)));
    }

    private function filter(Obj $filter): void
    {
        (new Search(
            $this->query,
            (array) $this->attribute($filter),
            $filter->get('value')
        ))->relations((array) $this->relation($filter))
            ->comparisonOperator(
                ComparisonOperators::get($this->config->get('comparisonOperator'))
            )
            ->searchMode(SearchModes::get($filter->get('mode')))
            ->handle();
    }

    private function attribute(Obj $filter): ?string
    {
        return $this->isNested($filter) ? null : $filter->get('data');
    }

    private function relation(Obj $filter): ?string
    {
        return $this->isNested($filter)
            ? Str::of($filter->get('data'))->after('.')
            : null;
    }

    private function isNested(string $attribute): bool
    {
        return Str::of($attribute)->explode('.')->count() > 1;
    }

    private function filterable(): Collection
    {
        return $this->config->columns()->filter(fn ($column) => $column
            ->get('meta')->get('filterable'));
    }

    private function filters(): Obj
    {
        return $this->config->searches()
            ->map(fn ($filters) => $filters
                ->filter(fn ($value) => $this->isValid($value)))
            ->filter->isNotEmpty();
    }

    private function isValid($value): bool
    {
        return ! Collection::wrap([null, ''])->containsStrict($value)
            && (! $value instanceof Collection || $value->isNotEmpty());
    }
}
