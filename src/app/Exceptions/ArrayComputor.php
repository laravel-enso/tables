<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class ArrayComputor extends EnsoException
{
    public static function missingInterface()
    {
        return new static(__(
            'Array computors must implement the "ComputesArrayColumns" interface'
        ));
    }
}
