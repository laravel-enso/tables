<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class Meta extends EnsoException
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

    public static function missingInterface()
    {
        return new static(__(
            'To use "rawTotal" the table builder must implement the "RawTotal" interface',
        ));
    }
}
