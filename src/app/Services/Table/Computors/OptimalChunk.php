<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

class OptimalChunk
{
    public static function get($count)
    {
        if ($count < 10000) {
            return 1000;
        }

        if ($count < 50000) {
            return 2000;
        }

        if ($count < 250000) {
            return 4000;
        }

        if ($count < 1250000) {
            return 10000;
        }

        return 20000;
    }
}
