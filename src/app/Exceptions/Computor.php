<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class Computor extends EnsoException
{
    public static function missingInterface()
    {
        return new static(__(
            'Computors must implement the "ComputesColumns" interface',
        ));
    }
}
