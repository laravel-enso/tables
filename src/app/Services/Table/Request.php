<?php

namespace LaravelEnso\Tables\app\Services\Table;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Table\Computors\Computors;

class Request
{
    private $request;
    private $fetchMode;

    public function __construct(array $request = [], $fetchMode = false)
    {
        $this->setRequest($request);
        $this->setMeta();
        $this->setColumns();

        $this->fetchMode = $fetchMode;
    }

    public function meta()
    {
        return $this->request->get('meta');
    }

    public function columns()
    {
        return $this->request->get('columns');
    }

    public function fetchMode()
    {
        return $this->fetchMode;
    }

    public function __call($method, $args)
    {
        return $this->request->{$method}(...$args);
    }

    private function setRequest($request)
    {
        $this->request = new Obj(json_decode(json_encode($request)));
    }

    private function setMeta()
    {
        $this->request->set(
            'meta', new Obj($this->array($this->request->get('meta')))
        );
    }

    private function setColumns()
    {
        $this->request->set(
            'columns',
            $this->request->get('columns', collect())
                ->map(function ($column) {
                    return new Obj($this->array($column));
                })
        );

        Computors::columns($this);
    }

    private function array($arg)
    {
        return is_string($arg)
            ? json_decode($arg, true)
            : $arg;
    }
}
