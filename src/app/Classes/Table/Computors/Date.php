<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table\Computors;

use Carbon\Carbon;

class Date
{
    private static $columns;

    public static function compute($row)
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] = ! is_null($row[$column->get('name')])
                ? Carbon::parse($row[$column->get('name')])
                    ->format(self::format($column))
                : $row[$column->get('name')];
        }

        return $row;
    }

    private static function format($column)
    {
        return $column->has('dateFormat')
            ? $column->get('dateFormat')
            : config('enso.datatable.dateFormat');
    }

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->get('meta')->get('date');
        });
    }
}
