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
        $this->query->where(function ($query) {
            $this->filters()->each(function ($filters, $table) use ($query) {
                $filters->each(function ($value, $column) use ($table, $query) {
                    $query->whereIn($table.'.'.$column,
                        collect($value)->toArray());
                });
            });
        });
    }

    private function filters()
    {
        return $this->config->filters()->map(function ($filters) {
            return $filters->filter(function ($value, $column) {
                return $this->isValid($value);
            });
        });
    }

    private function isValid($value)
    {
        return $value !== null
            && $value !== ''
            && ! ($value instanceof Collection && $value->isEmpty());
    }
}
