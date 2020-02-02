<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class ModelComputor extends EnsoException
{
    public static function missingInterface()
    {
        return new static(__(
            'Model computors must implement the "ComputesModelColumns" interface'
        ));
    }
}
