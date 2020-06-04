<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\ComputesArrayColumns;

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
            if ($row[$column->get('name')]) {
                $row[$column->get('name')] = $column
                    ->get('enum')[$row[$column->get('name')]];
            }
        }

        return $row;
    }
}
