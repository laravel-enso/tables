<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

class Translator
{
    public static $columns;

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->get('meta')->has('translatable');
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
