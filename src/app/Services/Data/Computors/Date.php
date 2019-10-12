<?php

namespace LaravelEnso\Tables\app\Services\Data\Computors;

use Carbon\Carbon;
use LaravelEnso\Tables\app\Contracts\ComputesColumns;

class Date implements ComputesColumns
{
    private static $columns;

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->get('meta')->get('date');
        });
    }

    public static function handle($row)
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
