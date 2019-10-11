<?php

namespace LaravelEnso\Tables\app\Services\Data;

use LaravelEnso\Helpers\app\Classes\Obj;

class Request
{
    private $columns;
    private $meta;
    private $filters;
    private $intervals;
    private $params;

    public function __construct($columns, $meta, $filters, $intervals, $params)
    {
        $this->columns = new Obj($this->parse($columns));
        $this->meta = new Obj($this->parse($meta));
        $this->filters = new Obj($this->parse($filters));
        $this->intervals = new Obj($this->parse($intervals));
        $this->params = new Obj($this->parse($params));
    }

    public function columns()
    {
        return $this->columns;
    }

    public function meta()
    {
        return $this->meta;
    }

    public function filters()
    {
        return $this->filters;
    }

    public function intervals()
    {
        return $this->intervals;
    }

    public function params()
    {
        return $this->params;
    }

    private function parse($arg)
    {
        return ! is_array($arg)
            ? $this->decode($arg)
            : collect($arg)->map(function($arg) {
                return $this->decode($arg);
            })->toArray(); 
    }

    private function decode($arg)
    {
        $argDecoded = json_decode($arg);

        return json_last_error() == JSON_ERROR_NONE
            ? $argDecoded
            : $arg;
    }
}
