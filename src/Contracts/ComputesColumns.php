<?php

namespace LaravelEnso\Tables\Contracts;

interface ComputesColumns
{
    public static function columns($columns): void;
}
