<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LaravelEnso\VueDatatable\app\Classes\Table\EnumComputor;
use LaravelEnso\VueDatatable\app\Classes\Table\Filters;

class Table
{
    private $request;
    private $filter;
    private $query;
    private $count;
    private $filtered;
    private $total;
    private $data;
    private $meta;

    public function __construct(Request $request, Builder $query)
    {
        $this->request = $request;
        $this->filter = new Filters($request, $query);
        $this->meta = json_decode($request->get('meta'));
        $this->query = $query;
        $this->total = collect();
    }

    public function data()
    {
        $this->run();

        return [
            'count'    => $this->count,
            'filtered' => $this->filtered,
            'total'    => $this->total,
            'data'     => $this->data,
        ];
    }

    private function run()
    {
        $this->filterdCount = $this->count = $this->count();

        if ($this->hasFilters()) {
            $this->filter->set();
            $this->filtered = $this->count();
        }

        $this->sort()
            ->setTotal()
            ->limit()
            ->setData()
            ->setAppends()
            ->computeEnum();
    }

    private function count()
    {
        return $this->query->count();
    }

    private function sort()
    {
        if (!$this->meta->sort) {
            return $this;
        }

        collect($this->request->get('columns'))
            ->each(function ($column) {
                $column = json_decode($column);

                if ($column->meta->sortable && $column->meta->sort) {
                    $this->query->orderBy($column->name, $column->meta->sort);
                }
            });

        return $this;
    }

    private function setTotal()
    {
        if (!$this->meta->total) {
            return $this;
        }

        $this->total = collect($this->request->get('columns'))
            ->reduce(function ($total, $column) {
                $column = json_decode($column);

                if ($column->meta->total) {
                    $total[$column->name] = $this->query->sum($column->name);
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

    private function computeEnum()
    {
        if ($this->meta->enum) {
            (new EnumComputor($this->data, $this->request))->run();
        }

        return $this;
    }

    private function hasFilters()
    {
        return $this->request->has('query')
            || $this->request->has('filters')
            || $this->request->has('intervalFilters');
    }
}
