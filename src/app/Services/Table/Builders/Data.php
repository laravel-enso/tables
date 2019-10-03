<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use Illuminate\Support\Arr;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Computors\Computors;
use LaravelEnso\Tables\app\Services\Table\Filters\CustomFilter;

class Data
{
    private $table;
    private $request;
    private $query;
    private $data;

    public function __construct(Table $table, Request $request)
    {
        $this->table = $table;
        $this->request = $request;
        $this->query = $this->table->query();
    }

    public function data()
    {
        $this->build();

        return $this->data;
    }

    private function build()
    {
        $this->filter()
            ->sort()
            ->limit()
            ->setData();

        if ($this->data->isNotEmpty()) {
            $this->setAppends()
                ->sanitize()
                ->compute()
                ->flatten();
        }
    }

    private function filter()
    {
        (new Filters($this->request, $this->query))
            ->custom($this->table instanceof CustomFilter)
            ->handle();

        return $this;
    }

    private function sort()
    {
        (new Sort($this->request, $this->query))->handle();

        return $this;
    }

    private function limit()
    {
        $this->query->skip($this->request->meta()->get('start'))
            ->take($this->request->meta()->get('length'));

        return $this;
    }

    private function setData()
    {
        $this->data = $this->query->get();

        return $this;
    }

    private function setAppends()
    {
        if ($this->request->has('appends')) {
            $this->data->each->setAppends(
                $this->request->get('appends')->toArray()
            );
        }

        return $this;
    }

    private function sanitize()
    {
        $this->data = collect($this->data->toArray());

        return $this;
    }

    private function compute()
    {
        $this->data = Computors::handle($this->request, $this->data);

        return $this;
    }

    private function flatten()
    {
        if ($this->request->get('flatten')) {
            $this->data = collect($this->data)->map(function ($record) {
                return Arr::dot($record);
            });
        }
    }
}
