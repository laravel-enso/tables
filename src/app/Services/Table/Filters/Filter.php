<?php

namespace LaravelEnso\Tables\app\Services\Table\Filters;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\app\Classes\Obj;

class Filter extends BaseFilter
{
    public function handle(): bool
    {
        if ($this->request->filled('filters')) {
            $this->filter();
        }

        return $this->filters;
    }

    private function filter()
    {
        $this->query->where(function ($query) {
            $this->parse('filters')->each(function ($filters, $table) use ($query) {
                $filters->each(function ($value, $column) use ($table, $query) {
                    if ($this->filterIsValid($value)) {
                        $query->whereIn(
                            $table.'.'.$column,
                            $value instanceof Collection ? $value->toArray() : (array) $value
                        );

                        $this->filters = true;
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

    private function parse($type)
    {
        return is_string($this->request->get($type))
            ? new Obj(json_decode($this->request->get($type), true))
            : $this->request->get($type);
    }
}
