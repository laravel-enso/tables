<?php

namespace LaravelEnso\Tables\Services\Data;

use Illuminate\Support\Collection;
use LaravelEnso\Tables\Contracts\ComputesColumns;

abstract class Computors
{
    protected static array $computors = [];

    public static function handle(Config $config, Collection $data): Collection
    {
        static::columns($config);

        return static::applicable($config)->reduce(fn ($data, $computor) => $data
            ->map(fn ($row) => static::computor($computor)::handle($row)), $data);
    }

    public static function columns(Config $config): void
    {
        static::applicable($config)
            ->each(fn ($computor) => static::computor($computor)::columns($config->columns()));
    }

    public static function computors(array $computors): void
    {
        static::$computors = $computors;
    }

    abstract protected static function computor($computor): ComputesColumns;

    protected static function applicable(Config $config): Collection
    {
        return $config->meta()->filter()->keys()
            ->intersect(Collection::wrap(static::$computors)->keys());
    }
}
