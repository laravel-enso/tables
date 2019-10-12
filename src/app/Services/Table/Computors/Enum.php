<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

use ReflectionClass;
use LaravelEnso\Enums\app\Services\Enum as EnsoEnum;

class Enum
{
    private static $columns;

    public static function compute($row)
    {
        foreach (self::$columns as $column) {
            $class = $column->get('enum');
            $enum = new $class();

            if ($row[$column->get('name')] !== null) {
                $row[$column->get('name')] = $enum::get($row[$column->get('name')]);
            }
        }

        return $row;
    }

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->has('enum')
                && (new ReflectionClass($column->get('enum')))
                    ->isSubclassOf(EnsoEnum::class);
        });
    }
}
