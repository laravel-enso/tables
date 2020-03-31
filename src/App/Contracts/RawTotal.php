<?php

namespace LaravelEnso\Tables\App\Contracts;

use LaravelEnso\Helpers\App\Classes\Obj;

interface RawTotal
{
    public function rawTotal(Obj $column): string;
}
