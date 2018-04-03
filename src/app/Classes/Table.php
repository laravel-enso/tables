<?php

namespace LaravelEnso\VueDatatable\app\Classes;

use LaravelEnso\VueDatatable\app\Classes\Table\Builder;

abstract class Table
{
    protected $request;
    protected $templatePath = '';

    abstract public function query();

    public function init()
    {
        return (new Template($this->templatePath))
            ->get();
    }

    public function data(array $request)
    {
        return (new Builder($request, $this->query()))
            ->data();
    }

    public function excel(array $request)
    {
        return (new Builder($request, $this->query()))
            ->excel();
    }
}
