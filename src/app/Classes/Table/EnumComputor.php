<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

class EnumComputor
{
    private $columns;
    private $data;
    private $enums;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;

        $this->setEnums();
    }

    public function get()
    {
        return collect($this->data)
            ->map(function ($record) {
                $this->enums->each(function ($column) use (&$record) {
                    $enum = new $column->enum();
                    $record[$column->name] = $enum::get($record[$column->name]);
                });

                return $record;
            });
    }

    private function setEnums()
    {
        $this->enums = collect();

        $this->columns->each(function ($column) {
            if (property_exists($column, 'enum')) {
                $this->enums->push($column);
            }
        });
    }
}
