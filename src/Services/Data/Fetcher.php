<?php

namespace LaravelEnso\Tables\Services\Data;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\Services\OptimalChunk;
use LaravelEnso\Tables\Contracts\Table;
use LaravelEnso\Tables\Services\Data\Builders\Data;
use LaravelEnso\Tables\Services\Data\Builders\Meta;

class Fetcher
{
    private Collection $data;
    private int $page;
    private bool $ready;
    private int $count;

    public function __construct(
        private Table $table,
        private Config $config
    ) {
        $this->data = new Collection();
        $this->page = 0;
        $this->ready = false;

        ArrayComputors::serverSide();
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

    public function count(): int
    {
        if (! isset($this->count)) {
            $this->count = (new Meta($this->table, $this->config))
                ->filter()->count(true);
        }

        return $this->count;
    }

    private function fetch($page = 0): Collection
    {
        $start = $this->config->meta()->get('length') * $page;
        $this->config->meta()->set('start', $start);

        return (new Data($this->table, $this->config, true))->build();
    }

    private function optimalChunk(): void
    {
        $optimalChunk = OptimalChunk::get($this->count());
        $this->config->meta()->set('length', $optimalChunk);

        $this->ready = true;
    }
}
