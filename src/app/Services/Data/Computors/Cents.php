<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\ComputesArrayColumns;

class Cents implements ComputesArrayColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns->filter(fn ($column) => $column->get('meta')->get('cents'));
    }

    public static function handle(array $row): array
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] /= 100;
        }

        return $row;
    }
}
