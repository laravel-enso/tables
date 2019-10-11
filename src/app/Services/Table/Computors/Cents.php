<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

use LaravelEnso\Tables\app\Contracts\ComputesColumns;

class Cents implements ComputesColumns
{
    private static $columns;

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->get('meta')->get('cents');
        });
    }

    public static function handle($row)
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] /= 100;
        }

        return $row;
    }
}
