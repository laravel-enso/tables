<?php

namespace LaravelEnso\Tables\app\Contracts;

use LaravelEnso\Helpers\app\Classes\Obj;

interface RawTotal
{
    public function rawTotal(Obj $column): string;
}
