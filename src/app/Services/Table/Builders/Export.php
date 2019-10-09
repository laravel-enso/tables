<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Services\Table\Computors\OptimalChunk;

class Export
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function fetcher()
    {
        $this->config->meta()
            ->set('length', OptimalChunk::get($this->count()));

        return $this;
    }

    public function fetch($page = 0)
    {
        $this->config->meta()->set(
            'start',
            $this->config->meta()->get('length') * $page
        );

        return $this->data();
    }

    private function count()
    {
        return (new Meta($this->config))
            ->count();
    }

    private function data()
    {
        return (new Data($this->config))
            ->data();
    }
}
