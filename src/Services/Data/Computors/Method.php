<?php

namespace LaravelEnso\Tables\Services\Data\Computors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Tables\Contracts\ComputesModelColumns;

class Method implements ComputesModelColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns
            ->filter(fn ($column) => $column->has('meta')
                && $column->get('meta')->get('method'))
            ->values();
    }

    public static function handle(Model $row)
    {
        foreach (self::$columns as $column) {
            $method = self::segments($column->get('name'))->last();
            $row->{$column->get('name')} = self::segments($column->get('name'))
                ->slice(0, -1)->reduce(fn ($value, $segment) => $value->{$segment}, $row)
                ->{$method}();
        }

        return $row;
    }

    private static function segments(string $attribute): Collection
    {
        return Str::of($attribute)->explode('.');
    }
}
