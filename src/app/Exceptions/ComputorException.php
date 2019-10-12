<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class ComputorException extends EnsoException
{
    public static function missingInterface()
    {
        return new static(__(
            'Computors must implement the "ComputesColumns" interface',
        ));
    }
}
