<?php

namespace LaravelEnso\Tables\App\Services\Data;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;

class Request
{
    private Obj $columns;
    private Obj $meta;
    private Obj $internalFilters;
    private Obj $filters;
    private Obj $intervals;
    private Obj $params;

    public function __construct($columns, $meta, $internalFilters, $filters, $intervals, $params)
    {
        $this->columns = new Obj($this->parse($columns));
        $this->meta = new Obj($this->parse($meta));
        $this->internalFilters = new Obj($this->parse($internalFilters));
        $this->filters = new Obj($this->parse($filters));
        $this->intervals = new Obj($this->parse($intervals));
        $this->params = new Obj($this->parse($params));
    }

    public function columns(): Obj
    {
        return $this->columns;
    }

    public function meta(): Obj
    {
        return $this->meta;
    }

    public function internalFilters(): Obj
    {
        return $this->internalFilters;
    }

    public function filters(): Obj
    {
        return $this->filters;
    }

    public function intervals(): Obj
    {
        return $this->intervals;
    }

    public function params(): Obj
    {
        return $this->params;
    }

    public function column(string $name): ?Obj
    {
        return $this->columns
            ->first(fn ($column) => $column->get('name') === $name);
    }

    private function parse($arg)
    {
        return ! is_array($arg)
            ? $this->decode($arg)
            : (new Collection($arg))->map(fn ($arg) => $this->decode($arg))
            ->toArray();
    }

    private function decode($arg)
    {
        if (is_array($arg)) {
            return $arg;
        }

        $decodedArg = json_decode($arg);

        return json_last_error() === JSON_ERROR_NONE
            ? $decodedArg
            : $arg;
    }
}
