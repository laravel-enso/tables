<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use Illuminate\Support\Arr;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Filters;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Computors;

class Data
{
    private $table;
    private $template;
    private $request;
    private $query;
    private $data;

    public function __construct(Table $table, Request $request, Template $template)
    {
        $this->table = $table;
        $this->request = $request;
        $this->template = $template;
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
            ->custom($this->table)
            ->handle();

        return $this;
    }

    private function sort()
    {
        if ($this->request->meta()->get('sort')) {
            (new Sort($this->request, $this->query))->handle();
        }

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
        if ($this->template->has('appends')) {
            $this->data->each->setAppends(
                $this->template->get('appends')->toArray()
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
        $this->data = Computors::handle($this->template, $this->data);

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
