<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use Carbon\Carbon;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\ComputesColumns;

class Date implements ComputesColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns->filter(fn ($column) => $column->get('meta')->get('date'));
    }

    public static function handle($row): array
    {
        foreach (self::$columns as $column) {
            if ($row[$column->get('name')] !== null) {
                $row[$column->get('name')] = Carbon::parse($row[$column->get('name')])
                    ->format(self::format($column));
            }
        }

        return $row;
    }

    private static function format($column)
    {
        return $column->has('dateFormat')
            ? $column->get('dateFormat')
            : config('enso.tables.dateFormat');
    }
}
