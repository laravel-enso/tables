<?php

namespace LaravelEnso\Tables\app\Services\Data\Filters;

use Illuminate\Support\Collection;

class Filter extends BaseFilter
{
    public function applies(): bool
    {
        return $this->config->filters()
            ->first(function($value) {
                return $this->filterIsValid($value);
            }) !== null;
    }

    public function handle()
    {
        $this->query->where(function ($query) {
            $this->config->filters()->each(function ($filters, $table) use ($query) {
                $filters->each(function ($value, $column) use ($table, $query) {
                    if ($this->filterIsValid($value)) {
                        $arrayValue = $value instanceof Collection
                            ? $value->toArray()
                            : (array) $value;

                        $query->whereIn($table.'.'.$column, $arrayValue);
                    }
                });
            });
        });
    }

    private function filterIsValid($value)
    {
        return $value !== null
            && $value !== ''
            && ! ($value instanceof Collection && $value->isEmpty());
    }
}
