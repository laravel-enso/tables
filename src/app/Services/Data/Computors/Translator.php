<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\ComputesColumns;

class Translator implements ComputesColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns->filter(fn ($column) => $column
            ->get('meta')->get('translatable'));
    }

    public static function handle($row): array
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] = __($row[$column->get('name')]);
        }

        return $row;
    }
}
