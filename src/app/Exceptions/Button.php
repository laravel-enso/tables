<?php

namespace LaravelEnso\Tables\App\Exceptions;

use LaravelEnso\Helpers\App\Exceptions\EnsoException;
use LaravelEnso\Tables\App\Attributes\Button as ButtonAttributes;

class Button extends EnsoException
{
    public static function wrongFormat()
    {
        return new static(__('The buttons array may contain only strings and objects'));
    }

    public static function undefined(string $buttons)
    {
        return new static(__(
            'Unknown Button(s) Found: ":buttons"',
            ['buttons' => $buttons]
        ));
    }

    public static function missingAttributes()
    {
        return new static(__(
            'The following attributes are mandatory for buttons: ":attrs"',
            ['attrs' => implode('", "', ButtonAttributes::Mandatory)],
        ));
    }

    public static function unknownAttributes()
    {
        return new static(__(
            'The following optional attributes are allowed for buttons: ":attrs"',
            ['attrs' => implode('", "', ButtonAttributes::Optional)]
        ));
    }

    public static function missingRoute()
    {
        return new static(__(
            'When you set an action for a button you need to provide the fullRoute or routeSuffix'
        ));
    }

    public static function missingMethod()
    {
        return new static(__(
            'When you set an ajax action for a button you need to provide the method aswell'
        ));
    }

    public static function wrongAction()
    {
        return new static(__(
            'The following actions are allowed for buttons: ":actions"',
            ['actions' => implode('", "', ButtonAttributes::Actions)]
        ));
    }

    public static function routeNotFound(string $route)
    {
        return new static(__(
            'Button route does not exist: ":route"',
            ['route' => $route]
        ));
    }

    public static function invalidMethod(string $method)
    {
        return new static(__(
            'Method is incorrect: ":method"',
            ['method' => $method]
        ));
    }
}
