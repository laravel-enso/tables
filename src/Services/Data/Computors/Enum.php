<?php

namespace LaravelEnso\Tables\Services\Data\Computors;

use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\ComputesArrayColumns;

class Enum implements ComputesArrayColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns
            ->filter(fn ($column) => $column->get('enum'))
            ->values();
    }

    public static function handle(array $row): array
    {
        foreach (self::$columns as $column) {
            $value = $column->get('enum')[data_get($row, $column->get('name'))] ?? null;
            data_set($row, $column->get('name'), $value);
        }

        return $row;
    }
}
