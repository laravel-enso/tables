<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;

class MetaException extends EnsoException
{
    public static function unknownAttributes($attrs)
    {
        return new static(__(
            'Unknown Meta Parameter(s): ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function unsupported($column)
    {
        return new static(__(
            'Nested columns do not support "sortable": ":column"',
            ['column' => $column]
        ));
    }
}
