<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Tables\App\Contracts\ComputesColumns;

class Resource implements ComputesColumns
{
    private static Obj $columns;

    public static function columns($columns): void
    {
        self::$columns = $columns->filter(fn ($column) => $column->get('resource'));
    }

    public static function handle($row): array
    {
        foreach (self::$columns as $column) {
            $resource = $column->get('resource');
            $objectMap = self::objectMap($row[$column->get('name')]);

            $row[$column->get('name')] = is_array($objectMap)
                ? $resource::collection($objectMap)
                : new $resource($objectMap);
        }

        return $row;
    }

    private static function objectMap($resource)
    {
        return is_array($resource)
            ? (new Collection($resource))
            ->map(fn ($model) => (object) $model)
            ->toArray()
            : (object) $resource;
    }
}
