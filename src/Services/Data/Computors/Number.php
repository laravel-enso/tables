<?php

namespace LaravelEnso\Tables\Services\Data\Computors;

use Illuminate\Support\Facades\App;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\ComputesArrayColumns;
use NumberFormatter as Formatter;

class Number implements ComputesArrayColumns
{
    private static Obj $columns;
    private static Formatter $formatter;

    public static function columns($columns): void
    {
        self::$columns = $columns
            ->filter(fn ($column) => $column->has('number'))
            ->values();
    }

    public static function handle(array $row): array
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] = self::format(
                $row[$column->get('name')],
                $column->get('number')->get('precision')
            );
        }

        return $row;
    }

    public static function format($value, $precision = 0)
    {
        if (! isset(self::$formatter)) {
            self::$formatter = new Formatter(App::getLocale(), Formatter::DECIMAL);
        }

        self::$formatter->setAttribute(Formatter::FRACTION_DIGITS, $precision);

        return self::$formatter->format($value);
    }
}
