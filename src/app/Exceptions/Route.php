<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;

class Route extends EnsoException
{
    public static function notFound(string $route)
    {
        return new static(__(
            'Read route does not exist: ":route"',
            ['route' => $route]
        ));
    }
}
