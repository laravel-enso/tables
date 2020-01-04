<?php

namespace LaravelEnso\Tables\App\Services\Data;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\App\Contracts\Table;
use LaravelEnso\Tables\App\Services\Data\Builders\Data;
use LaravelEnso\Tables\App\Services\Data\Builders\Meta;
use LaravelEnso\Tables\App\Services\Data\Computors\OptimalChunk;

class Fetcher
{
    private Config $config;
    private Table $table;
    private Collection $data;
    private int $page;
    private bool $ready;

    public function __construct(Table $table, Config $config)
    {
        $this->config = $config;
        $this->table = $table;
        $this->data = new Collection();
        $this->page = 0;
        $this->ready = false;

        Computors::fetchMode();
    }

    public function current(): Collection
    {
        return $this->data;
    }

    public function chunkSize(): int
    {
        return $this->data->count();
    }

    public function next(): void
    {
        if (! $this->ready) {
            $this->optimalChunk();
        }

        $this->data = $this->fetch($this->page);
        $this->page++;
    }

    public function valid(): bool
    {
        return $this->data->isNotEmpty();
    }

    private function fetch($page = 0): Collection
    {
        $this->config->meta()->set(
            'start', $this->config->meta()->get('length') * $page
        );

        return (new Data($this->table, $this->config))->data();
    }

    private function optimalChunk(): void
    {
        $this->config->meta()->set(
            'length', OptimalChunk::get($this->count())
        );

        $this->ready = true;
    }

    private function count(): int
    {
        return (new Meta($this->table, $this->config))->count();
    }
}
