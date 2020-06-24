<?php

namespace LaravelEnso\Tables\Services\Data\Computors;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class OptimalChunk
{
    public const Thresholds = [
        ['limit' => 10 * 1000, 'chunk' => 1000],
        ['limit' => 50 * 1000, 'chunk' => 2 * 1000],
        ['limit' => 250 * 1000, 'chunk' => 4 * 1000],
        ['limit' => 1.25 * 1000 * 1000, 'chunk' => 10 * 1000],
    ];

    public const MaxChunk = 20000;

    public static function get($count): int
    {
        $sheetLimit = Config::get('enso.tables.export.sheetLimit');

        $match = (new Collection(self::Thresholds))
            ->first(fn ($threshold) => $count <= $threshold['limit']);

        $limit = $match ? $match['chunk'] : self::MaxChunk;

        return min($limit, $sheetLimit);
    }
}
