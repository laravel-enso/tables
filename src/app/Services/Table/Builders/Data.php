<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use Illuminate\Support\Arr;
use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Services\Table\Filters;
use LaravelEnso\Tables\app\Services\Table\Computors\Computors;

class Data
{
    private $config;
    private $query;
    private $data;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->query = $config->table()->query();
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
        (new Filters($this->config, $this->query))
            ->handle();

        return $this;
    }

    private function sort()
    {
        (new Sort($this->config, $this->query))->handle();

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

    private function setAppends()
    {
        if ($this->config->has('appends')) {
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
            $this->data = collect($this->data)->map(function ($record) {
                return Arr::dot($record);
            });
        }
    }
}
