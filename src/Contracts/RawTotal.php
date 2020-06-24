<?php

namespace LaravelEnso\Tables\Contracts;

use LaravelEnso\Helpers\Services\Obj;

interface RawTotal
{
    public function rawTotal(Obj $column): string;
}
