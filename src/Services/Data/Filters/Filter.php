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
                    ->whereIn("{$table}.{$column}", $value))));
    }

    private function filters(): Obj
    {
        return $this->config->filters()->map
            ->filter(fn ($value) => $this->isValid($value))
            ->filter->isNotEmpty()->map
            ->map(fn ($value) => $this->value($value));
    }

    private function isValid($value): bool
    {
        return ! Collection::wrap([null, ''])->containsStrict($value)
            && (! $value instanceof Collection || $value->isNotEmpty());
    }

    private function value($value): array
    {
        return $value instanceof Collection
            ? $value->toArray()
            : (array) $value;
    }
}
