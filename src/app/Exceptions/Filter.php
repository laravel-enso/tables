<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class Filter extends EnsoException
{
    public static function invalidClass($class)
    {
        return new static(
            __(':class must implement "LaravelEnso\Tables\app\Contracts\Filter"', ['class' => $class])
        );
    }
}
