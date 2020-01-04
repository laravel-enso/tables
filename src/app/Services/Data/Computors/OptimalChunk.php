<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

use Illuminate\Support\Collection;

class OptimalChunk
{
    public const ChunkPerLimit = [
        [10 * 1000, 1000],
        [50 * 1000, 2 * 1000],
        [250 * 1000, 4 * 1000],
        [1.25 * 1000 * 1000, 10 * 1000],
    ];

    public const MaxChunk = 20000;

    public static function get($count): int
    {
        $sheetLimit = config('enso.tables.export.sheetLimit');

        $match = (new Collection(self::ChunkPerLimit))
            ->first(fn ($limit) => $count <= $limit[0]);

        $limit = $match ? $match[1] : self::MaxChunk;

        return min($limit, $sheetLimit);
    }
}
