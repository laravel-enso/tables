<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Computors\OptimalChunk;

class Export
{
    private $request;
    private $table;

    public function __construct(Table $table, Request $request)
    {
        $this->request = $request;
        $this->table = $table;
    }

    public function fetcher()
    {
        $this->request->meta()
            ->set('length', OptimalChunk::get($this->count()));

        return $this;
    }

    public function fetch($page = 0)
    {
        $this->request->meta()->set(
            'start',
            $this->request->meta()->get('length') * $page
        );

        return $this->data();
    }

    private function count()
    {
        return (new Meta($this->table, $this->request))
            ->count();
    }

    private function data()
    {
        return (new Data($this->table, $this->request))
            ->data();
    }
}
