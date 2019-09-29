<?php

namespace LaravelEnso\Tables\app\Services;

use LaravelEnso\Tables\app\Services\Table\Request;
use LaravelEnso\Tables\app\Contracts\Table as TableData;
use LaravelEnso\Tables\app\Services\Table\Builders\Data;
use LaravelEnso\Tables\app\Services\Table\Builders\Export;

abstract class Table implements TableData
{
    protected $request;
    protected $templatePath;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    abstract public function query();

    /** @deprecated */
    public function request()
    {
        return $this->request;
    }

    /** @deprecated */
    public function init()
    {
        return (new TemplateCache($this))
            ->get();
    }

    /** @deprecated */
    public function data()
    {
        return (new Data($this, new Request($this->request->toArray())))
            ->data();
    }

    /** @deprecated */
    public function fetcher()
    {
        return (new Export($this, new Request($this->request->toArray())))
            ->fetcher();
    }

    public function templatePath()
    {
        return $this->templatePath;
    }
}
