<?php

namespace LaravelEnso\Tables\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;
use LaravelEnso\Tables\Attributes\Button as Attributes;

class Button extends EnsoException
{
    public static function invalidFormat()
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
            ['attrs' => implode('", "', Attributes::Mandatory)],
        ));
    }

    public static function unknownAttributes()
    {
        return new static(__(
            'The following optional attributes are allowed for buttons: ":attrs"',
            ['attrs' => implode('", "', Attributes::Optional)]
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

    public static function missingName()
    {
        return new static(__(
            'When you use render action conditionally you need to provide name as well'
        ));
    }

    public static function invalidAction()
    {
        return new static(__(
            'The following actions are allowed for buttons: ":actions"',
            ['actions' => implode('", "', Attributes::Actions)]
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

    public static function noSelectable()
    {
        return new static(__(
            "You can't have an action with selection when the table is not selectable",
        ));
    }

    public static function rowSelection()
    {
        return new static(__(
            'Selection works only on global buttons',
        ));
    }
}
