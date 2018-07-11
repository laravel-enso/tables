<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

use LaravelEnso\Helpers\app\Classes\Obj;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use LaravelEnso\VueDatatable\app\Exceptions\QueryException;

class Builder
{
    private $request;
    private $query;
    private $count;
    private $filtered;
    private $total;
    private $data;
    private $columns;
    private $meta;
    private $fullRecordInfo;

    public function __construct(Obj $request, QueryBuilder $query)
    {
        $this->request = $request;
        $this->meta = is_string($this->request->get('meta'))
            ? json_decode($this->request->get('meta'))
            : (object) $this->request->get('meta');
        $this->query = $query;
        $this->total = collect();

        $this->setColumns();
    }

    public function fetcher(int $chunk)
    {
        $this->meta->length = $chunk;
        $this->filter();

        return $this;
    }

    public function fetch($page)
    {
        return $this->query
            ->skip($this->meta->length * $page)
            ->take($this->meta->length)
            ->pluck('dtRowId');
    }

    public function data()
    {
        $this->run();

        $this->checkActions();

        return [
            'count' => $this->count,
            'filtered' => $this->filtered,
            'total' => $this->total,
            'data' => $this->data,
            'fullRecordInfo' => $this->fullRecordInfo,
            'filters' => $this->hasFilters(),
        ];
    }

    public function excel()
    {
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

        $this->setDetailedInfo()
            ->filter()
            ->sort()
            ->setTotal()
            ->limit()
            ->setData()
            ->setAppends()
            ->toArray()
            ->computeEnum()
            ->computeDate();
    }

    private function checkActions()
    {
        if (count($this->data) === 0) {
            return;
        }

        if (!isset($this->data[0]['dtRowId'])) {
            throw new QueryException(__('You have to add in the main query \'id as "dtRowId"\' for the actions to work'));
        }
    }

    private function count()
    {
        return $this->query->count();
    }

    private function setDetailedInfo()
    {
        $this->fullRecordInfo = $this->hasFilters() && !optional($this->meta)->forceInfo
            ? $this->count <= config('enso.datatable.fullInfoRecordLimit')
            : true;

        return $this;
    }

    private function filter()
    {
        if ($this->hasFilters()) {
            (new Filters($this->request, $this->query, $this->columns))->set();

            if ($this->fullRecordInfo) {
                $this->filtered = $this->count();
            }
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
        if (!$this->meta->total || !$this->fullRecordInfo) {
            return $this;
        }

        $this->total = $this->columns
            ->reduce(function ($total, $column) {
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

        $this->data->each
            ->setAppends($this->request->get('appends'));

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
                if (is_string($column)) {
                    return json_decode($column);
                }

                $column = (object) $column;
                $column->meta = (object) $column->meta;

                return $column;
            });
    }

    private function hasFilters()
    {
        return $this->request->filled('search')
            || $this->request->has('filters')
            || $this->request->has('intervals');
    }
}
