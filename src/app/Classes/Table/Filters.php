<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

use Carbon\Carbon;

class Filters
{
    private $request;
    private $query;
    private $columns;

    public function __construct($request, $query, $columns)
    {
        $this->request = $request;
        $this->query = $query;
        $this->columns = $columns;
    }

    public function set()
    {
        $this->setSearch()
            ->setFilters()
            ->setIntervals();
    }

    private function setSearch()
    {
        if (!$this->request->filled('search')) {
            return $this;
        }

        collect(explode(' ', $this->request->get('search')))
            ->each(function ($arg) {
                $this->query->where(function ($query) use ($arg) {
                    $this->columns->each(function ($column) use ($query, $arg) {
                        if ($column->meta->searchable) {
                            $query->orWhere($column->data, 'LIKE', '%'.$arg.'%');
                        }
                    });
                });
            });

        return $this;
    }

    private function setFilters()
    {
        if (!$this->request->has('filters')) {
            return $this;
        }

        $this->query->where(function ($query) {
            collect($this->parse('filters'))
                ->each(function ($filters, $table) use ($query) {
                    collect($filters)->each(function ($value, $column) use ($table, $query) {
                        if (!is_null($value) && $value !== '' && $value !== []) {
                            $query->whereIn($table.'.'.$column, (array) $value);
                        }
                    });
                });
        });

        return $this;
    }

    private function setIntervals()
    {
        if (!$this->request->has('intervals')) {
            return $this;
        }

        $this->query->where(function () {
            collect($this->parse('intervals'))
                ->each(function ($interval, $table) {
                    collect($interval)
                        ->each(function ($value, $column) use ($table) {
                            $this->setMinLimit($table, $column, (object) $value)
                                ->setMaxLimit($table, $column, (object) $value);
                        });
                });
        });

        return $this;
    }

    private function setMinLimit($table, $column, $value)
    {
        if (is_null($value->min)) {
            return $this;
        }

        $min = property_exists($value, 'dbDateFormat')
            ? $this->formatDate($value->min, $value->dbDateFormat)
            : $value->min;

        $this->query->where($table.'.'.$column, '>=', $min);

        return $this;
    }

    private function setMaxLimit($table, $column, $value)
    {
        if (is_null($value->max)) {
            return $this;
        }

        $max = property_exists($value, 'dbDateFormat')
            ? $this->formatDate($value->max, $value->dbDateFormat)
            : $value->max;

        $this->query->where($table.'.'.$column, '<=', $max);

        return $this;
    }

    private function formatDate(string $date, string $dbDateFormat)
    {
        return (new Carbon($date))->format($dbDateFormat);
    }

    private function parse($type)
    {
        return is_string($this->request->get($type))
            ? json_decode($this->request->get($type))
            : (object) $this->request->get($type);
    }
}
