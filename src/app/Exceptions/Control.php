<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class Control extends EnsoException
{
    public static function invalidFormat()
    {
        return new static(__('The controls array may contain only strings.'));
    }

    public static function undefined($controls)
    {
        return new static(__(
            'Unknown control(s) Found: ":controls"',
            ['controls' => $controls]
        ));
    }
}
