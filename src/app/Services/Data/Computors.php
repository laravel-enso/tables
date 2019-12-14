<?php

namespace LaravelEnso\Tables\app\Services\Data;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\app\Contracts\ComputesColumns;
use LaravelEnso\Tables\app\Exceptions\Computor as Exception;
use LaravelEnso\Tables\app\Services\Data\Computors\Cents;
use LaravelEnso\Tables\app\Services\Data\Computors\Date;
use LaravelEnso\Tables\app\Services\Data\Computors\Enum;
use LaravelEnso\Tables\app\Services\Data\Computors\Translator;

class Computors
{
    private static $fetchMode = false;

    private static $computors = [
        'enum' => Enum::class,
        'cents' => Cents::class,
        'date' => Date::class,
        'translatable' => Translator::class,
    ];

    public static function handle(Config $config, Collection $data)
    {
        self::columns($config);

        $data = self::applicable($config)->reduce(function ($data, $computor) {
            return $data->map(function ($row) use ($computor) {
                return self::computor($computor)::handle($row);
            });
        }, $data);

        return $data;
    }

    public static function columns(Config $config)
    {
        self::applicable($config)->each(function ($computor) use ($config) {
            self::computor($computor)::columns($config->columns());
        });
    }

    public static function fetchMode()
    {
        self::$fetchMode = true;
    }

    public static function computors(array $computors)
    {
        self::$computors = $computors;
    }

    private static function computor($computor): ComputesColumns
    {
        $computor = new self::$computors[$computor]();

        if (! $computor instanceof ComputesColumns) {
            throw Exception::missingInterface();
        }

        return $computor;
    }

    private static function applicable(Config $config)
    {
        return $config->meta()->filter()->keys()
            ->intersect(collect(self::$computors)->keys())
            ->filter(function ($computor) {
                return $computor !== 'translatable' || self::$fetchMode;
            });
    }
}
