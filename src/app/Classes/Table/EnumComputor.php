<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

class EnumComputor
{
    private $columns;
    private $data;
    private $withEnum;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;

        $this->setWithEnum();
    }

    public function get()
    {
        return collect($this->data)->map(function ($record) {
            $this->withEnum->each(function ($column) use (&$record) {
                $enum = new $column->enum();
                $record[$column->name] = $enum::get($record[$column->name]);
            });

            return $record;
        });
    }

    private function setWithEnum()
    {
        $this->withEnum = collect();

        $this->columns->each(function ($column) {
            if (property_exists($column, 'enum')) {
                $this->withEnum->push($column);
            }
        });
    }
}
