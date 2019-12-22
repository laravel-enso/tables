<?php

namespace LaravelEnso\Tables\app\Services\Data\Filters;

use Illuminate\Support\Collection;

class Filter extends BaseFilter
{
    public function applies(): bool
    {
        return $this->filters()->isNotEmpty();
    }

    public function handle()
    {
        $this->query->where(fn($query) => (
           $this->filters()->each(fn($filters, $table) => (
               $filters->each(fn($value, $column) => (
                   $query->whereIn($table.'.'.$column, collect($value)->toArray())
               ))
           ))
        ));
    }

    private function filters()
    {
        return $this->config->filters()->map(fn($filters) => (
            $filters->filter(fn($value) => $this->isValid($value))
        ))->filter->isNotEmpty();
    }

    private function isValid($value)
    {
        return ! collect([null, ''])->containsStrict($value)
            && (! $value instanceof Collection || $value->isNotEmpty());
    }
}
