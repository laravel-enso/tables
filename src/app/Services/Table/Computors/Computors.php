<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\app\Services\Table\Config;

class Computors
{
    private static $computors = [
        'enum' => Enum::class,
        'cents' => Cents::class,
        'date' => Date::class,
        'translatable' => Translator::class,
    ];

    public static function handle(Config $config, Collection $data)
    {
        $data = self::computors($config)
            ->reduce(function ($data, $meta) {
                return $data->map(function ($row) use ($meta) {
                    return self::$computors[$meta]::handle($row);
                });
            }, $data);

        return $data;
    }

    public static function columns(Config $config)
    {
        self::computors($config)
            ->each(function ($meta) use ($config) {
                self::$computors[$meta]::columns($config->columns());
            });
    }

    private static function computors(Config $config)
    {
        return $config->meta()->filter()->keys()
            ->intersect(collect(self::$computors)->keys())
            ->filter(function ($computor) use ($config) {
                return $computor !== 'translatable' || $config->fetchMode();
            });
    }
}
