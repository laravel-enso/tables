<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Facades\App;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Config;
use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Services\Table\Computors;
use LaravelEnso\Tables\app\Services\Table\Builders\Export;

class Fetcher
{
    private $config;
    private $fetcher;
    private $data;
    private $page = 0;

    public function __construct(Table $table, Config $config)
    {
        $this->config = $config;

        $this->fetcher = (new Export($table, $this->config))->fetcher();
    }

    public function name()
    {
        $this->config->get('name');
    }

    public function data()
    {
        return $this->data;
    }

    public function chunkSize()
    {
        return $this->data->count();
    }

    public function next()
    {
        $this->data = $this->fetcher->fetch($this->page++);
    }

    public function valid()
    {
        return $this->data->isNotEmpty();
    }
}
