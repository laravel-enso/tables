<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\VueDatatable\app\Classes\Table\Builder;

abstract class Table
{
    protected $request;
    protected $templatePath = '';

    public function __construct(array $request = [])
    {
        $this->request = new Obj($request);
    }

    abstract public function query();

    public function request()
    {
        return $this->request;
    }

    public function init()
    {
        return (new Template($this->templatePath))
            ->get();
    }

    public function data()
    {
        return $this->builder()
            ->data();
    }

    public function excel()
    {
        return $this->builder()
            ->excel();
    }

    public function fetcher(int $chunk)
    {
        return $this->builder()
            ->fetcher($chunk);
    }

    private function builder()
    {
        return new Builder(
            $this->request,
            $this->query()
        );
    }
}
