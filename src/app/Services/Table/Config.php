<?php

namespace LaravelEnso\Tables\app\Services\Table;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template;
use LaravelEnso\Tables\app\Services\Table\Computors\Computors;

class Config
{
    private const TemplateProxy = ['appends', 'countCache', 'flatten'];
    private const ColumnMeta = ['visible', 'sort', 'hidden'];
    private const Meta = ['search', 'visible', 'searchMode', 'start', 'length',
        'translate', 'forceInfo', 'sort', ];

    private $request;
    private $fetchMode;
    private $template;
    private $columns;
    private $meta;

    public function __construct(array $request = [], $fetchMode = false)
    {
        $this->fetchMode = $fetchMode;
        $this->setRequest($request);
    }

    public function setTemplate(Template $template)
    {
        $this->template = $template;
        $this->setMeta();
        $this->setColumns();

        return $this;
    }

    public function meta()
    {
        return $this->meta;
    }

    public function columns()
    {
        return $this->columns;
    }

    public function table()
    {
        return $this->template->table();
    }

    public function fetchMode()
    {
        return $this->fetchMode;
    }

    public function params()
    {
        return new Obj(json_decode($this->request->get('params')));
    }

    public function __call($method, $args)
    {
        if (collect(self::TemplateProxy)->contains($args[0] ?? null)) {
            return $this->template->{$method}(...$args);
        }

        return $this->request->{$method}(...$args);
    }

    private function setRequest($request)
    {
        $this->request = new Obj(json_decode(json_encode($request)));
    }

    private function setMeta()
    {
        $this->meta = $this->template->meta()
            ->forget(static::Meta)
            ->merge($this->requestMeta());
    }

    private function setColumns()
    {
        $this->columns = $this->requestColumns()
            ->map(function ($column, $index) {
                return $this->mergeColumnMeta($this->template->column($index), $column);
            });

        Computors::columns($this);
    }

    private function mergeColumnMeta(Obj $templateColumn, Obj $requestColumn)
    {
        $meta = $templateColumn->get('meta', collect())
            ->forget(static::ColumnMeta)
            ->merge($this->requestColumnMeta($requestColumn));

        $templateColumn->set('meta', $meta);

        return $templateColumn;
    }

    private function requestColumns()
    {
        return $this->request->get('columns', collect())
            ->map(function ($column) {
                return new Obj($this->array($column));
            });
    }

    private function requestMeta()
    {
        return (new Obj($this->array($this->request->get('meta'))))
            ->intersectByKeys(collect(static::Meta)->flip());
    }

    private function requestColumnMeta(Obj $requestColumn)
    {
        return $requestColumn->get('meta', collect())
            ->intersectByKeys(collect(static::ColumnMeta)->flip());
    }

    private function array($arg)
    {
        return is_string($arg)
            ? json_decode($arg, true)
            : $arg;
    }
}
