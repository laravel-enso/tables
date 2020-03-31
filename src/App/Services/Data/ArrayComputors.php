<?php

namespace LaravelEnso\Tables\App\Services\Data;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\App\Contracts\ComputesArrayColumns;
use LaravelEnso\Tables\App\Exceptions\ArrayComputor;
use LaravelEnso\Tables\App\Services\Data\Computors\Cents;
use LaravelEnso\Tables\App\Services\Data\Computors\Date;
use LaravelEnso\Tables\App\Services\Data\Computors\DateTime;
use LaravelEnso\Tables\App\Services\Data\Computors\Enum;
use LaravelEnso\Tables\App\Services\Data\Computors\Translator;

class ArrayComputors extends Computors
{
    private static bool $serverSide = false;

    protected static array $computors = [
        'enum' => Enum::class,
        'cents' => Cents::class,
        'date' => Date::class,
        'datetime' => DateTime::class,
        'translatable' => Translator::class,
    ];

    public static function serverSide(): void
    {
        self::$serverSide = true;
    }

    protected static function computor($computor): ComputesArrayColumns
    {
        $computor = new static::$computors[$computor]();

        if (! $computor instanceof ComputesArrayColumns) {
            throw ArrayComputor::missingInterface();
        }

        return $computor;
    }

    protected static function applicable(Config $config): Collection
    {
        return parent::applicable($config)
            ->when(! self::$serverSide, fn ($computors) => $computors
                ->reject(fn ($computor) => in_array($computor, ['enum', 'translatable'])));
    }
}
