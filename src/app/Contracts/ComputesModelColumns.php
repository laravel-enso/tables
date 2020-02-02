<?php

namespace LaravelEnso\Tables\App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ComputesModelColumns extends ComputesColumns
{
    public static function handle(Model $row);
}
