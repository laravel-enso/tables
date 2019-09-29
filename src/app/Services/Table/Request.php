<?php

namespace LaravelEnso\Tables\app\Services\Table;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Table\Computors\Computors;

class Request
{
    private $request;
    private $columns;
    private $fetchMode;

    public function __construct(array $request = [], $fetchMode = false)
    {
        $this->fetchMode = $fetchMode;
        $this->request = new Obj(json_decode(json_encode($request)));
        $this->setMeta();
        $this->setColumns();
    }

    public function meta()
    {
        return $this->request->get('meta');
    }

    public function columns()
    {
        return $this->columns;
    }

    public function fetchMode()
    {
        return $this->fetchMode;
    }


    public function __call($method, $args)
    {
        return $this->request->$method(...$args);
    }

    private function setMeta()
    {
        $this->request->set(
            'meta',
            new Obj($this->array($this->request->get('meta')))
        );
    }

    private function setColumns()
    {
        $this->request->set(
            'columns',
            $this->request->get('columns', collect([]))
                ->map(function ($column) {
                    return new Obj($this->array($column));
                })
        );

        $this->columns = Computors::columns(
            $this->request->get('columns'),
            $this->request->get('meta'),
            $this->fetchMode
        );
    }

    private function array($arg)
    {
        return is_string($arg)
            ? json_decode($arg, true)
            : $arg;
    }
}
