<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

use LaravelEnso\Tables\app\Contracts\ComputesColumns;

class Translator implements ComputesColumns
{
    private static $columns;

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->get('meta')->get('translatable');
        });
    }

    public static function handle($row)
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] = __($row[$column->get('name')]);
        }

        return $row;
    }
}
