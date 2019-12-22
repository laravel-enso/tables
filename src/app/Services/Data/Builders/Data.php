<?php

namespace LaravelEnso\Tables\app\Services\Data\Builders;

use Illuminate\Support\Arr;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Data\Computors;
use LaravelEnso\Tables\app\Services\Data\Config;
use LaravelEnso\Tables\app\Services\Data\Filters;
use LaravelEnso\Tables\app\Services\Data\Sort;

class Data
{
    private $config;
    private $table;
    private $query;
    private $data;

    public function __construct(Table $table, Config $config)
    {
        $this->table = $table;
        $this->config = $config;
        $this->query = $this->table->query();
    }

    public function build()
    {
        $this->filter()
            ->sort()
            ->limit()
            ->setData();

        if ($this->data->isNotEmpty()) {
            $this->appends()
                ->sanitize()
                ->compute()
                ->flatten();
        }

        return $this;
    }

    public function toArray()
    {
        return ['data' => $this->data()];
    }

    public function data()
    {
        $this->build();

        return $this->data;
    }

    private function filter()
    {
        (new Filters(
            $this->table, $this->config, $this->query
        ))->handle();

        return $this;
    }

    private function sort()
    {
        if ($this->config->meta()->get('sort')) {
            (new Sort($this->config, $this->query))->handle();
        }

        return $this;
    }

    private function limit()
    {
        $this->query->skip($this->config->meta()->get('start'))
            ->take($this->config->meta()->get('length'));

        return $this;
    }

    private function setData()
    {
        $this->data = $this->query->get();

        return $this;
    }

    private function appends()
    {
        if ($this->config->filled('appends')) {
            $this->data->each->setAppends(
                $this->config->get('appends')->toArray()
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
        $this->data = Computors::handle($this->config, $this->data);

        return $this;
    }

    private function flatten()
    {
        if ($this->config->get('flatten')) {
            $this->data = collect($this->data)
                ->map(fn($record) => Arr::dot($record));
        }
    }
}
