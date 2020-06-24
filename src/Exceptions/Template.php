<?php

namespace LaravelEnso\Tables\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;

class Template extends EnsoException
{
    public static function missingAttributes(string $attrs)
    {
        return new static(__(
            'Mandatory Attribute(s) Missing: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function unknownAttributes(string $attrs)
    {
        return new static(__(
            'Unknown Attribute(s) Found: ":attrs"',
            ['attrs' => $attrs]
        ));
    }

    public static function invalidLengthMenu()
    {
        return new static(__('"lengthMenu" attribute must be an array'));
    }

    public static function invalidAppends()
    {
        return new static(__('"appends" attribute must be an array'));
    }

    public static function invalidSearchModes()
    {
        return new static(__('"searchModes" attribute must be an associative array'));
    }

    public static function invalidDebounce()
    {
        return new static(__('"debounce" attribute must be an integer'));
    }

    public static function invalidMethod()
    {
        return new static(__('"method" attribute can be either "GET" or "POST"'));
    }

    public static function invalidSelectable()
    {
        return new static(__('"selectable" attribute must be a boolean'));
    }

    public static function invalidComparisonOperator()
    {
        return new static(__(
            '"comparisonOperator" attribute can be either "LIKE" or "ILIKE"'
        ));
    }

    public static function invalidSearchMode()
    {
        return new static(__(
            '"searchMode" attribute can be one of "full", "startsWith" or "endsWith"'
        ));
    }
}
