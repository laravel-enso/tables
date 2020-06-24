<?php

namespace LaravelEnso\Tables\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ComputesModelColumns extends ComputesColumns
{
    public static function handle(Model $row);
}
