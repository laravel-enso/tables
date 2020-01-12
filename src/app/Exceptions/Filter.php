<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class Filter extends EnsoException
{
    public static function invalidClass(string $class)
    {
        return new static(__(
            ':class must implement "LaravelEnso\Tables\App\Contracts\Filter"',
            ['class' => $class]
        ));
    }
}
