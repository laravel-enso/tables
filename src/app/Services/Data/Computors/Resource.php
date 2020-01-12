<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\ComputesColumns;

class Resource implements ComputesColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns->filter(fn ($column) => $column->get('resource'));
    }

    public static function handle($row): array
    {
        foreach (self::$columns as $column) {
            $resource = $column->get('resource');
            $row[$column->get('name')] = new $resource($row[$column->get('name')]);
        }

        return $row;
    }
}
