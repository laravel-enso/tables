<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use Illuminate\Support\Arr;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Contracts\AfterCount;
use LaravelEnso\Tables\app\Services\Table\Filters;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Computors\Computors;

class Data
{
    private $table;
    private $request;
    private $query;
    private $filters;
    private $data;
    private $columns;
    private $meta;

    public function __construct(Table $table, Request $request)
    {
        $this->table = $table;
        $this->request = $request;
        $this->meta = $request->meta();
        $this->columns = $request->columns();
        $this->filters = false;
    }

    public function data()
    {
        $this->run();

        return $this->data;
    }

    private function run()
    {
        $this->query()
            ->filter()
            ->sort()
            ->limit()
            ->setData();

        if ($this->data->isNotEmpty()) {
            $this->setAppends()
                ->collect()
                ->computes()
                ->flatten();
        }
    }

    private function query()
    {
        $this->query = $this->table instanceof AfterCount
            ? $this->table->afterCount($this->table->query())
            : $this->table->query();

        return $this;
    }

    private function filter()
    {
        $this->filters = (new Filters(
            $this->request,
            $this->query
        ))->handle();

        return $this;
    }

    private function sort()
    {
        if (! $this->meta->get('sort')) {
            return $this;
        }

        $this->columns->each(function ($column) {
            if ($column->get('meta')->get('sortable') && $column->get('meta')->get('sort')) {
                $column->get('meta')->get('nullLast')
                    ? $this->query->orderByRaw($this->rawSort($column))
                    : $this->query->orderBy(
                        $column->get('data'),
                        $column->get('meta')->get('sort')
                    );
            }
        });

        return $this;
    }

    private function rawSort($column)
    {
        return "({$column->get('data')} IS NULL),"
            ."{$column->get('data')} {$column->get('meta')->get('sort')}";
    }

    private function limit()
    {
        $this->query->skip($this->meta->get('start'))
            ->take($this->meta->get('length'));

        return $this;
    }

    private function setData()
    {
        $this->data = $this->query->get();

        return $this;
    }

    private function setAppends()
    {
        if (! $this->request->has('appends')) {
            return $this;
        }

        $this->data->each->setAppends(
            $this->request->get('appends')->toArray()
        );

        return $this;
    }

    private function collect()
    {
        $this->data = collect($this->data->toArray());

        return $this;
    }

    private function computes()
    {
        $this->data = Computors::compute(
            $this->data, $this->meta, $this->request->fetchMode()
        );

        return $this;
    }

    private function flatten()
    {
        if (! $this->request->get('flatten')) {
            return;
        }

        $this->data = collect($this->data)
            ->map(function ($record) {
                return Arr::dot($record);
            });
    }
}
