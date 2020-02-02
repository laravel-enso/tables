<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\ComputesArrayColumns;

class Translator implements ComputesArrayColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns->filter(fn ($column) => $column
            ->get('meta')->get('translatable'));
    }

    public static function handle(array $row): array
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] = __($row[$column->get('name')]);
        }

        return $row;
    }
}
