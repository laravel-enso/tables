<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class Export extends EnsoException
{
    public static function alreadyRunning()
    {
        return new static(__('An export job is already running for the same table'));
    }
}
