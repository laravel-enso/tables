<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class QueryException extends EnsoException
{
    public static function unknownSearchMode()
    {
        return new static(__('Unknown search mode provided in request'));
    }
}
