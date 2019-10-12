<?php

namespace LaravelEnso\Tables\app\Services\Data\Computors;

use LaravelEnso\Tables\app\Contracts\ComputesColumns;

class Enum implements ComputesColumns
{
    private static $columns;

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->get('enum');
        });
    }

    public static function handle($row)
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] = $column
                ->get('enum')::get($row[$column->get('name')]);
        }

        return $row;
    }
}
