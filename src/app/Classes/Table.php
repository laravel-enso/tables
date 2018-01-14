<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use LaravelEnso\VueDatatable\app\Classes\Table\Filters;
use LaravelEnso\VueDatatable\app\Classes\Table\DateComputor;
use LaravelEnso\VueDatatable\app\Classes\Table\EnumComputor;
use LaravelEnso\VueDatatable\app\Exceptions\ExportException;
use LaravelEnso\VueDatatable\app\Classes\Table\ExportComputor;

class Table
{
    private $request;
    private $query;
    private $count;
    private $filtered;
    private $total;
    private $data;
    private $columns;
    private $meta;

    public function __construct(Request $request, Builder $query)
    {
        $this->request = $request;
        $this->meta = json_decode($request->get('meta'));
        $this->query = $query;
        $this->total = collect();

        $this->setColumns();
    }

    public function data()
    {
        $this->run();

        return [
            'count' => $this->count,
            'filtered' => $this->filtered,
            'total' => $this->total,
            'data' => $this->data,
        ];
    }

    public function excel()
    {
        $this->checkExportLimit();

        $this->run();

        $export = new ExportComputor($this->data, $this->columns);

        return [
            'name' => $this->request->get('name'),
            'header' => $this->columns->pluck('label')->toArray(),
            'data' => $export->data()->toArray(),
        ];
    }

    private function run()
    {
        $this->filtered = $this->count = $this->count();

        $this->filter()
            ->sort()
            ->setTotal()
            ->limit()
            ->setData()
            ->setAppends()
            ->toArray()
            ->computeEnum()
            ->computeDate();
    }

    private function count()
    {
        return $this->query->count();
    }

    private function filter()
    {
        if ($this->hasFilters()) {
            (new Filters($this->request, $this->query, $this->columns))->set();
            $this->filtered = $this->count();
        }

        return $this;
    }

    private function sort()
    {
        if (!$this->meta->sort) {
            return $this;
        }

        $this->columns->each(function ($column) {
            if ($column->meta->sortable && $column->meta->sort) {
                $this->query->orderBy($column->data, $column->meta->sort);
            }
        });

        return $this;
    }

    private function setTotal()
    {
        if (!$this->meta->total) {
            return $this;
        }

        $this->total = $this->columns->reduce(function ($total, $column) {
            if ($column->meta->total) {
                $total[$column->name] = $this->query->sum($column->data);
            }

            return $total;
        }, []);

        return $this;
    }

    private function limit()
    {
        $this->query->skip($this->meta->start)
            ->take($this->meta->length);

        return $this;
    }

    private function setData()
    {
        $this->data = $this->query->get();

        return $this;
    }

    private function setAppends()
    {
        if (!$this->request->has('appends')) {
            return $this;
        }

        $this->data->each->setAppends($this->request->get('appends'));

        return $this;
    }

    private function toArray()
    {
        $this->data = $this->data->toArray();

        return $this;
    }

    private function computeEnum()
    {
        if ($this->meta->enum) {
            $this->data = (new EnumComputor($this->data, $this->columns))->get();
        }

        return $this;
    }

    private function computeDate()
    {
        if ($this->meta->date) {
            $this->data = (new DateComputor($this->data, $this->columns))->get();
        }
    }

    private function setColumns()
    {
        $this->columns = collect($this->request->get('columns'))
            ->map(function ($column) {
                return json_decode($column);
            });
    }

    private function hasFilters()
    {
        return $this->request->has('search')
            || $this->request->has('filters')
            || $this->request->has('intervalFilters');
    }

    private function checkExportLimit()
    {
        if ($this->meta->length > config('enso.datatable.export.limit')) {
            throw new ExportException(__(
                'The table exceeds the maximum number of records allowed: :actual vs :limit',
                ['actual' => $this->meta->length, 'limit' => config('enso.datatable.export.limit')]
            ));
        }
    }
}
