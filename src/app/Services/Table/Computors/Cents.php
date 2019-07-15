<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

class Cents
{
    private static $columns;

    public static function compute($row)
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] = $row[$column->get('name')] / 100;
        }

        return $row;
    }

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->get('meta')->has('cents');
        });
    }
}
