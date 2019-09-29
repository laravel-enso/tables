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
        $meta->filter()->keys()->each(function ($meta) use ($fetchMode, $columns) {
            if ($meta === 'translatable' && ! $fetchMode) {
                return;
            }

            if (isset(self::$computors[$meta])) {
                self::$computors[$meta]::columns($columns);
            }
        });

        return $columns;
    }

    public static function compute($data, $meta, $fetchMode)
    {
        $data = $meta->filter()->keys()->reduce(function ($data, $meta) use ($fetchMode) {
            if ($meta === 'translatable' && ! $fetchMode) {
                return;
            }

            return $data->map(function ($row) use ($meta) {
                return isset(self::$computors[$meta])
                    ? self::$computors[$meta]::compute($row)
                    : $row;
            });
        }, $data);

        return $data;
    }
}
