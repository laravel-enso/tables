<?php

namespace LaravelEnso\Tables\App\Services\Data;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\App\Contracts\ComputesColumns;
use LaravelEnso\Tables\App\Exceptions\Computor as Exception;
use LaravelEnso\Tables\App\Services\Data\Computors\Cents;
use LaravelEnso\Tables\App\Services\Data\Computors\Date;
use LaravelEnso\Tables\App\Services\Data\Computors\Enum;
use LaravelEnso\Tables\App\Services\Data\Computors\Translator;

class Computors
{
    private static bool $fetchMode = false;

    private static array $computors = [
        'enum' => Enum::class,
        'cents' => Cents::class,
        'date' => Date::class,
        'translatable' => Translator::class,
    ];

    public static function handle(Config $config, Collection $data): Collection
    {
        self::columns($config);

        return self::applicable($config)->reduce(fn ($data, $computor) => $data
            ->map(fn ($row) => self::computor($computor)::handle($row)), $data);
    }

    public static function columns(Config $config): void
    {
        self::applicable($config)
            ->each(fn ($computor) => self::computor($computor)::columns($config->columns()));
    }

    public static function fetchMode(): void
    {
        self::$fetchMode = true;
    }

    public static function computors(array $computors): void
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

    private static function applicable(Config $config): Collection
    {
        return $config->meta()->filter()->keys()
            ->intersect((new Collection(self::$computors))->keys())
            ->filter(fn ($computor) => $computor !== 'translatable' || self::$fetchMode);
    }
}
