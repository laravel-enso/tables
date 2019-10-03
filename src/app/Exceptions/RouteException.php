<?php

namespace LaravelEnso\Tables\app\Exceptions;

use LaravelEnso\Helpers\app\Exceptions\EnsoException;
use Doctrine\Common\Annotations\Annotation\Attributes;

class RouteException extends EnsoException
{
    public static function notFound($route)
    {
        return new static(__(
            'Read route does not exist: ":route"',
            ['route' => $route]
        ));
    }
}
