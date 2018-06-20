<?php

namespace LaravelEnso\VueDatatable\app\Classes;

abstract class Action
{
    private $builder;
    private $request;
    private $class;
    private $chunk;
    private $data;
    private $page = 0;

    abstract public function process();

    public function request(array $request)
    {
        $this->request = $request;

        return $this;
    }

    public function class(string $class)
    {
        $this->class = $class;

        return $this;
    }

    public function chunk(int $chunk)
    {
        $this->chunk = $chunk;

        return $this;
    }

    public function run()
    {
        $this->init();

        while ($this->next()) {
            $this->process();
        }
    }

    public function data()
    {
        return $this->data;
    }

    private function next()
    {
        $this->data = $this->builder
            ->fetch($this->page++);

        return !$this->data->isEmpty();
    }

    private function init()
    {
        $this->builder = (new $this->class($this->request))
            ->fetcher($this->chunk);
    }
}
