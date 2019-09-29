<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

class Computors
{
    private static $computors = [
        'enum' => Enum::class,
        'cents' => Cents::class,
        'date' => Date::class,
        'translatable' => Translatable::class,
    ];

    public static function columns($columns, $meta, $fetchMode)
    {
        self::metaComputors($meta, $fetchMode)
            ->each(function ($meta) use ($columns) {
                self::$computors[$meta]::columns($columns);
            });

        return $columns;
    }

    public static function compute($data, $meta, $fetchMode)
    {
        $data = self::metaComputors($meta, $fetchMode)
            ->reduce(function ($data, $meta) {
                return $data->map(function ($row) use ($meta) {
                    return self::$computors[$meta]::compute($row);
                });
            }, $data);

        return $data;
    }

    private static function metaComputors($meta, $fetchMode)
    {
        return $meta->filter()
            ->keys()
            ->intersect(collect(self::$computors)->keys())
            ->filter(function ($computor) use ($fetchMode) {
                return $computor !== 'translatable' || $fetchMode;
            });
    }
}
