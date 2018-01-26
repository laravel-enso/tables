<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

class ExportComputor
{
    private $data;
    private $columns;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function data()
    {
        $columns = $this->getColumns();

        return collect($this->data)->map(function ($record) use ($columns) {
            return $columns->reduce(function ($collector, $column) use ($record) {
                $collector[$column->name] = $column->translation
                    ? __($record[$column->name])
                    : $record[$column->name];

                return $collector;
            }, []);
        });
    }

    private function getColumns()
    {
        return $this->columns->reduce(function ($columns, $column) {
            $columns->push((object) [
                'name' => $column->name,
                'translation' => $column->meta->translation,
            ]);

            return $columns;
        }, collect());
    }
}
