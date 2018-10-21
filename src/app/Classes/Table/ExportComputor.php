<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table;

use LaravelEnso\Helpers\app\Classes\Obj;

class ExportComputor
{
    private $data;
    private $columns;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $this->columns($columns);
    }

    public function data()
    {
        return collect($this->data)
            ->map(function ($record) {
                return $this->columns
                    ->reduce(function ($collector, $column) use ($record) {
                        $collector[$column->name] = $column->translation
                            ? __($record[$column->name])
                            : $record[$column->name];

                        return $collector;
                    }, []);
            });
    }

    private function columns($columns)
    {
        return $columns
            ->reduce(function ($columns, $column) {
                if (! $column->meta->notExportable && ! $column->meta->rogue) {
                    $columns->push(new Obj([
                        'name' => $column->name,
                        'translation' => $column->meta->translation,
                    ]));
                }

                return $columns;
            }, collect());
    }
}
