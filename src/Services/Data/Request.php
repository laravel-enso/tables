<?php

namespace LaravelEnso\Tables\Services\Data;

use JsonException;
use LaravelEnso\Helpers\Services\Obj;

class Request
{
    private Obj $columns;
    private Obj $meta;
    private Obj $searches;
    private Obj $filters;
    private Obj $intervals;
    private Obj $params;

    public function __construct($columns, $meta, FilterAggregator $aggregator)
    {
        $this->columns = new Obj($this->sanitize($columns));
        $this->meta = new Obj($this->sanitize($meta));
        $this->searches = $aggregator->searches();
        $this->filters = $aggregator->filters();
        $this->intervals = $aggregator->intervals();
        $this->params = $aggregator->params();
    }

    public function columns(): Obj
    {
        return $this->columns;
    }

    public function meta(): Obj
    {
        return $this->meta;
    }

    public function searches(): Obj
    {
        return $this->searches;
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

    /**
     * @throws JsonException
     */
    private function sanitize(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }
}
