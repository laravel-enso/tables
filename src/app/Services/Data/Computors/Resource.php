<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\ComputesModelColumns;

class Resource implements ComputesModelColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns->filter(fn ($column) => $column->get('resource'));
    }

    public static function handle(Model $row)
    {
        foreach (self::$columns as $column) {
            $row[$column->get('name')] = self::resource($row, $column);
        }

        return $row;
    }

    private static function resource($row, $column)
    {
        if ($row[$column->get('name')] === null) {
            return;
        }

        $resource = $column->get('resource');
        $value = $row[$column->get('name')];

        return $row[$column->get('name')] === $value instanceof EloquentCollection
            ? $resource::collection($value)
            : new $resource($value);
    }
}
