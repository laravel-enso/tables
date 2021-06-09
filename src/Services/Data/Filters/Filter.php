<?php

namespace LaravelEnso\Tables\Services\Data\Filters;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\Obj;

class Filter extends BaseFilter
{
    public function applies(): bool
    {
        return $this->filters()->isNotEmpty();
    }

    public function handle(): void
    {
        $this->query->where(fn ($query) => $this->filters()
            ->each(fn ($filters, $table) => $filters
                ->each(fn ($value, $column) => $query
                    ->whereIn($table.'.'.$column, Collection::wrap($value)->toArray()))));
    }

    private function filters(): Obj
    {
        return $this->config->filters()->map(fn ($filters) => $filters
            ->filter(fn ($value) => $this->isValid($value)))
            ->filter->isNotEmpty();
    }

    private function isValid($value): bool
    {
        return ! Collection::wrap([null, ''])->containsStrict($value)
            && (! $value instanceof Collection || $value->isNotEmpty());
    }
}
