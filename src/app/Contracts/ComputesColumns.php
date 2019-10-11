<?php

namespace LaravelEnso\Tables\app\Contracts;

interface ComputesColumns
{
    public static function columns($columns);

    public static function handle($row);
}
