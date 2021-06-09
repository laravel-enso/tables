<?php

namespace LaravelEnso\Tables\Services\Data;

use Illuminate\Support\Collection;

class RequestArgument
{
    public static function parse($arg)
    {
        return ! is_array($arg)
            ? self::decode($arg)
            : Collection::wrap($arg)->map(fn ($arg) => self::decode($arg))->toArray();
    }

    public static function decode($arg)
    {
        if (is_array($arg)) {
            return $arg;
        }

        $decodedArg = json_decode($arg, true);

        return json_last_error() === JSON_ERROR_NONE
            ? $decodedArg
            : $arg;
    }
}
