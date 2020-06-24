<?php

namespace LaravelEnso\Tables\Services\Data\Computors;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\ComputesArrayColumns;

class DateTime implements ComputesArrayColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns
            ->filter(fn ($column) => $column->get('meta')->get('datetime'))
            ->values();
    }

    public static function handle(array $row): array
    {
        foreach (self::$columns as $column) {
            if ($row[$column->get('name')] !== null) {
                $row[$column->get('name')] = Carbon::parse($row[$column->get('name')])
                    ->setTimezone(Config::get('app.timezone'))
                    ->format(self::format($column));
            }
        }

        return $row;
    }

    private static function format($column)
    {
        return $column->has('dateFormat')
            ? $column->get('dateFormat')
            : Config::get('enso.tables.dateTimeFormat');
    }
}
