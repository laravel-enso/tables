<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

use LaravelEnso\VueDatatable\app\Exceptions\ExportException;

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

        return $this->data->map(function ($record) use ($columns) {
            return $columns->reduce(function ($collector, $column) use ($record) {
                $this->checkType($record, $column->name);

                $collector[$column->name] = $column->translation
                    ? __($record->{$column->name})
                    : $record->{$column->name};

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

    private function checkType($record, $columnName)
    {
        if (is_scalar($record->{$columnName}) || is_null($record->{$columnName})) {
            return;
        }

        throw new ExportException(__(sprintf(
            'The export only accepts scalar and null values while "%s" is of type "%s"',
            $columnName,
            gettype($record->{$columnName})
        )), 555);
    }
}
