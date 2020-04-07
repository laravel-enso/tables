<?php

namespace LaravelEnso\Tables\App\Services\Data\Filters;

use Illuminate\Support\Collection;
use LaravelEnso\Filters\App\Services\Search;
use LaravelEnso\Helpers\App\Classes\Obj;

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
        (new Search($this->query, [$filter->get('data')], $filter->get('value')))
            ->comparisonOperator($this->config->get('comparisonOperator'))
            ->searchMode($filter->get('mode'))
            ->handle();
    }

    private function filterable(): Collection
    {
        return $this->config->columns()->filter(fn ($column) => $column
            ->get('meta')->get('filterable'));
    }

    private function filters(): Obj
    {
        return $this->config->searches()->map(fn ($filters) => $filters
            ->filter(fn ($value) => $this->isValid($value)))
            ->filter->isNotEmpty();
    }

    private function isValid($value): bool
    {
        return ! (new Collection([null, '']))->containsStrict($value)
            && (! $value instanceof Collection || $value->isNotEmpty());
    }
}
