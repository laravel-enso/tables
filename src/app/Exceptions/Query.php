<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class Query extends EnsoException
{
    public static function unknownSearchMode()
    {
        return new static(__('Unknown search mode provided in request'));
    }
}
