<?php

namespace LaravelEnso\Tables\App\Contracts;

interface ComputesColumns
{
    public static function columns($columns): void;
}
