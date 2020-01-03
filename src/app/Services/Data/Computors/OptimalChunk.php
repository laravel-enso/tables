<?php

namespace LaravelEnso\Tables\App\Services\Data\Computors;

class OptimalChunk
{
    public static function get($count): int
    {
        $sheetLimit = config('enso.tables.export.sheetLimit');

        if ($count < 10000) {
            return min(1000, $sheetLimit);
        }

        if ($count < 50000) {
            return min(2000, $sheetLimit);
        }

        if ($count < 250000) {
            return min(4000, $sheetLimit);
        }

        if ($count < 1250000) {
            return min(10000, $sheetLimit);
        }

        return min(20000, $sheetLimit);
    }
}
