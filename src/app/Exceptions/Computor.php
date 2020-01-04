<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class Computor extends EnsoException
{
    public static function missingInterface()
    {
        return new static(__(
            'Computors must implement the "ComputesColumns" interface',
        ));
    }
}
