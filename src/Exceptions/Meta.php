<?php

namespace LaravelEnso\Tables\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;

class Meta extends EnsoException
{
    public static function unknownAttributes(string $attrs)
    {
        return new static(__(
            'Unknown Meta Parameter(s): ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function unsupported(string $column)
    {
        return new static(__(
            'Nested columns do not support "sortable": ":column"',
            ['column' => $column]
        ));
    }

    public static function cannotFilterIcon(string $column)
    {
        return new static(__(
            'Icon columns do not support "fiterable": ":column"',
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
