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
            $resource = $column->get('resource');

            $row[$column->get('name')] = $row[$column->get('name')] instanceof EloquentCollection
                ? $resource::collection($row[$column->get('name')])
                : new $resource($row[$column->get('name')]);
        }

        return $row;
    }
}
