<?php

namespace LaravelEnso\Tables\App\Services\Data\Filters;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;

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
                    ->whereIn($table.'.'.$column, (new Collection($value))->toArray()))));
    }

    private function filters(): Obj
    {
        return $this->config->filters()->map(fn ($filters) => $filters
            ->filter(fn ($value) => $this->isValid($value)))
            ->filter->isNotEmpty();
    }

    private function isValid($value): bool
    {
        return ! (new Collection([null, '']))->containsStrict($value)
            && (! $value instanceof Collection || $value->isNotEmpty());
    }
}
