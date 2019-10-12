<?php

namespace LaravelEnso\Tables\app\Services\Data;

use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Data\Builders\Data;
use LaravelEnso\Tables\app\Services\Data\Builders\Meta;
use LaravelEnso\Tables\app\Services\Data\Computors\OptimalChunk;

class Fetcher
{
    private $config;
    private $table;
    private $data;
    private $page;
    private $ready;

    public function __construct(Table $table, Config $config)
    {
        $this->config = $config;
        $this->table = $table;
        $this->data = collect();
        $this->page = 0;
        $this->ready = false;

        Computors::fetchMode();
    }

    public function current()
    {
        return $this->data;
    }

    public function chunkSize()
    {
        return $this->data->count();
    }

    public function next()
    {
        if (! $this->ready) {
            $this->setOptimalChunk();
        }

        $this->data = $this->fetch($this->page++);
    }

    public function valid()
    {
        return $this->data->isNotEmpty();
    }

    private function fetch($page = 0)
    {
        $this->config->meta()->set(
            'start', $this->config->meta()->get('length') * $page
        );

        return (new Data($this->table, $this->config))->data();
    }

    private function setOptimalChunk()
    {
        $this->config->meta()->set(
            'length', OptimalChunk::get($this->count())
        );

        $this->ready = true;
    }

    private function count()
    {
        return (new Meta($this->table, $this->config))->count();
    }
}
