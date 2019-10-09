<?php

namespace LaravelEnso\Tables\app\Services;

use Illuminate\Support\Facades\App;
use LaravelEnso\Tables\app\Services\Table\Config;
use LaravelEnso\Tables\app\Services\Table\Builders\Export;

class Fetcher
{
    private $config;
    private $builder;
    private $data;
    private $page = 0;

    public function __construct(string $class, array $request)
    {
        $this->config = new Config($request, true);
        $table = App::make($class, ['config' => $this->config]);

        $this->builder = (new Export(
            $this->config->setTemplate(TemplateLoader::load($table))
        ))->fetcher();
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
        $this->data = $this->builder->fetch($this->page++);
    }

    public function valid()
    {
        return $this->data->isNotEmpty();
    }

    public function config()
    {
        return $this->config;
    }
}
