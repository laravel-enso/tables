<?php

namespace LaravelEnso\Tables\app\Services\Table;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use LaravelEnso\Helpers\app\Classes\Obj;

class Filters
{
    private $request;
    private $query;
    private $columns;
    private $filters;

    public function __construct($request, $query, $columns)
    {
        $this->request = $request;
        $this->query = $query;
        $this->columns = $columns;
        $this->filters = false;
    }

    public function handle()
    {
        $this->setSearch()
            ->setFilters()
            ->setIntervals()
            ->checkParams();

        return $this->filters;
    }

    private function setSearch()
    {
        if (! $this->request->get('meta')->filled('search')) {
            return $this;
        }

        collect(explode(' ', $this->request->get('meta')->get('search')))
            ->each(function ($arg) {
                $this->query->where(function ($query) use ($arg) {
                    $this->columns->each(function ($column) use ($query, $arg) {
                        if ($column->get('meta')->get('searchable')) {
                            $query->orWhere(
                                $column->get('data'),
                                $this->request->get('meta')->get('comparisonOperator'), '%'.$arg.'%'
                            );
                        }
                    });
                });
            });
        $this->filters = true;

        return $this;
    }

    private function setFilters()
    {
        if (! $this->request->filled('filters')) {
            return $this;
        }

        $this->query->where(function ($query) {
            $this->parse('filters')
                ->each(function ($filters, $table) use ($query) {
                    $filters->each(function ($value, $column) use ($table, $query) {
                        if ($this->filterIsValid($value)) {
                            if ($value instanceof Collection) {
                                $value = $value->toArray();
                            }

                            $query->whereIn($table.'.'.$column, (array) $value);
                            $this->filters = true;
                        }
                    });
                });
        });

        return $this;
    }

    private function setIntervals()
    {
        if (! $this->request->filled('intervals')) {
            return $this;
        }

        $this->query->where(function () {
            $this->parse('intervals')
                ->each(function ($interval, $table) {
                    collect($interval)
                        ->each(function ($value, $column) use ($table) {
                            $this->setMinLimit($table, $column, $value)
                                ->setMaxLimit($table, $column, $value);
                        });
                });
        });

        return $this;
    }

    private function checkParams()
    {
        if ($this->request->filled('params')) {
            $this->filters = true;
        }
    }

    private function filterIsValid($value)
    {
        return $value !== null
            && $value !== ''
            && ! ($value instanceof Collection && $value->isEmpty());
    }

    private function setMinLimit($table, $column, $value)
    {
        if ($value->get('min') === null) {
            return $this;
        }

        $dateFormat = $value->get('dateFormat')
            ?? config('enso.config.dateFormat');

        $dbDateFormat = $value->get('dbDateFormat');

        $min = $dateFormat || $dbDateFormat
            ? $this->formatDate(
                $value->get('min'), $dateFormat, $dbDateFormat
            ) : $value->get('min');
        $this->query->where($table.'.'.$column, '>=', $min);

        $this->filters = true;

        return $this;
    }

    private function setMaxLimit($table, $column, $value)
    {
        if ($value->get('max') === null) {
            return $this;
        }

        $dateFormat = $value->get('dateFormat')
            ?? config('enso.config.dateFormat');

        $dbDateFormat = $value->get('dbDateFormat');

        $max = $dateFormat || $dbDateFormat
            ? $this->formatDate(
                $value->get('max'), $dateFormat, $dbDateFormat
            ) : $value->get('max');

        $this->query->where($table.'.'.$column, '<=', $max);

        $this->filters = true;

        return $this;
    }

    private function formatDate(string $date, $dateFormat, $dbDateFormat)
    {
        $date = $dateFormat
            ? Carbon::createFromFormat($dateFormat, $date)
            : new Carbon($date);

        return $dbDateFormat
            ? $date->format($dbDateFormat)
            : $date;
    }

    private function parse($type)
    {
        return is_string($this->request->get($type))
            ? new Obj(json_decode($this->request->get($type), true))
            : $this->request->get($type);
    }
}
