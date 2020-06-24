<?php

namespace LaravelEnso\Tables\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;

class Control extends EnsoException
{
    public static function invalidFormat()
    {
        return new static(__('The controls array may contain only strings'));
    }

    public static function undefined(string $controls)
    {
        return new static(__(
            'Unknown control(s) Found: ":controls"',
            ['controls' => $controls]
        ));
    }
}
