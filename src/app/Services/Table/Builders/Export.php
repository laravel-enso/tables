<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Config;
use LaravelEnso\Tables\app\Services\Table\Computors;
use LaravelEnso\Tables\app\Services\Table\Computors\OptimalChunk;

class Export
{
    private $table;
    private $config;

    public function __construct(Table $table, Config $config)
    {
        $this->table = $table;
        $this->config = $config;

        Computors::fetchMode();
    }

    public function fetcher()
    {
        $this->config->meta()->set(
            'length', OptimalChunk::get($this->count())
        );

        return $this;
    }

    public function fetch($page = 0)
    {
        $this->config->meta()->set(
            'start', $this->config->meta()->get('length') * $page
        );

        return $this->data();
    }

    private function count()
    {
        return (new Meta($this->table, $this->config))->count();
    }

    private function data()
    {
        return (new Data($this->table, $this->config))->data();
    }
}
